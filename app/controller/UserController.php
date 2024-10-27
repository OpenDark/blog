<?php

namespace app\controller;

use app\model\User;
use app\model\Follow;
use app\model\Comment;
use app\model\Message;
use app\model\Favorite;
use support\Response;
use think\db\Query;
use think\facade\Db;

class UserController extends BaseController
{
    protected bool|array $needLogin = [
        'doFollow',
        'message',
        'doMessage',
        'upload',
    ];

    /* 用户 */
    public function index(int $id): Response
    {
        $user = $this->getUserDetail($id);
        return view('user/index', [
            'title' => '用户文章 - ' . $user['nickname'],
            'user' => $user,
            'id' => $id,
        ]);
    }

    private function getUserDetail(int $id): array
    {
        $user = $this->getUserInfo($id);
        empty($user) && $this->failure('用户不存在');
        $user['group'] = $this->userGroup[$user['role']];

        // 是否关注
        $user['is_follow'] = 0;
        if ($this->userInfo) {
            $cancel = Follow::where([
                'from_user_id' => $this->userInfo['id'],
                'to_user_id' => $user['id']
            ])->value('is_cancel');
            is_null($cancel) && $cancel = 1;
            $user['is_follow'] = 1 - $cancel;
        }
        return $user;
    }

    /* 文章作者 */
    public function getUser(int $id): Response
    {
        $data['user'] = $this->getUserInfo($id);
        if (empty($data['user'])) {
            $data['user']['id'] = 0;
            $data['user']['role'] = 1;
        }
        $data['user']['group'] = $this->userGroup[$data['user']['role']];
        $data['article'] = $this->getArticleLatest($id);

        // 是否关注
        $data['is_follow'] = 0;
        if ($this->userInfo && $data['user']['id']) {
            $cancel = Follow::where([
                'from_user_id' => $this->userInfo['id'],
                'to_user_id' => $data['user']['id']
            ])->value('is_cancel');
            is_null($cancel) && $cancel = 1;
            $data['is_follow'] = 1 - $cancel;
        }
        return json(['code' => 200, 'msg' => 'success', 'data' => $data]);
    }

    /* 关注 */
    public function doFollow()
    {
        $id = $this->request->post('id');
        if ($id == $this->userInfo['id']) {
            $this->failure('不能关注自己');
        }
        $cancel = 0;
        $where = [
            'from_user_id' => $this->userInfo['id'],
            'to_user_id' => $id
        ];
        $follow = Follow::where($where)->findOrEmpty();

        Db::startTrans();
        try {
            if ($follow->isEmpty()) {
                Follow::create($where);
            } else {
                $cancel = $follow['is_cancel'] = 1 - $follow['is_cancel'];
                $follow->save();
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->failure('系统错误：关注失败');
        }

        $this->success([
            'id' => $id,
            'cancel' => $cancel
        ]);
    }

    /* 评论 */
    public function comment(int $id): Response
    {
        $user = $this->getUserDetail($id);
        $comment = Comment::field('id,article_id,content,created_at')
            ->where('user_id', $id)->with([
                'article' => function (Query $query) {
                    $query->field('id,title');
                }
            ])->select()->toArray();

        return view('user/comment', [
            'title' => '用户评论 - ' . $user['nickname'],
            'comment' => $comment,
            'user' => $user,
            'id' => $id,
        ]);
    }

    /* 评论列表 */
    public function getComment(): Response
    {
        $id = $this->request->input('id', 0);
        $comment = Comment::field('id,article_id,content,created_at')
            ->where('user_id', $id)->with([
                'article' => function (Query $query) {
                    $query->field('id,title');
                }
            ])->select()->toArray();
        return json(['code' => 200, 'msg' => 'success', 'data' => $comment]);
    }

    /* 关注 */
    public function follow(int $id): Response
    {
        $user = $this->getUserDetail($id);
        return view('user/follow', [
            'title' => '用户关注 - ' . $user['nickname'],
            'user' => $user,
            'id' => $id,
        ]);
    }

    /* 关注列表 */
    public function getFollow(): Response
    {
        $id = $this->request->input('id', 0);
        $follow = Follow::field('id,to_user_id')
            ->where('from_user_id', $id)->where('is_cancel', 0)->with([
                'user' => function (Query $query) {
                    $query->field('id,nickname,avatar,desc,article_num,comment_num,fans_num');
                }
            ])->select()->toArray();

        // 当前用户关注
        $my_follow = Follow::where('from_user_id', $id)
            ->where('is_cancel', 0)->column('to_user_id');
        foreach ($follow as $key => $val) {
            $follow[$key]['is_follow'] = in_array($val['to_user_id'], $my_follow) ? 1 : 0;
        }

        return json(['code' => 200, 'msg' => 'success', 'data' => $follow]);
    }

    /* 粉丝列表 */
    public function getFans(): Response
    {
        $id = $this->request->input('id', 0);
        $fans = Follow::field('id,from_user_id')
            ->where('to_user_id', $id)->where('is_cancel', 0)->with([
                'fans' => function (Query $query) {
                    $query->field('id,nickname,avatar,desc,article_num,comment_num,fans_num');
                }
            ])->select()->toArray();

        // 当前用户关注
        $my_follow = Follow::where('from_user_id', $id)
            ->where('is_cancel', 0)->column('to_user_id');
        foreach ($fans as $key => $val) {
            $fans[$key]['is_follow'] = in_array($val['from_user_id'], $my_follow) ? 1 : 0;
        }

        return json(['code' => 200, 'msg' => 'success', 'data' => $fans]);
    }

    /* 收藏 */
    public function favorite(int $id): Response
    {
        $user = $this->getUserDetail($id);
        return view('user/favorite', [
            'title' => '用户收藏 - ' . $user['nickname'],
            'user' => $user,
            'id' => $id,
        ]);
    }

    /* 收藏文章列表 */
    public function getArticleFavorite(): Response
    {
        $id = $this->request->input('id', 0);
        $ids = Favorite::where('user_id', $id)->column('article_id');
        if ($ids) {
            $where[] = ['id', 'in', $ids];
            $data = $this->getArticleList($where);
        } else {
            $data = [
                'current_page' => 1,
                'data' => [],
                'last_page' => 0,
                'per_page' => 10,
                'total' => 0,
            ];
        }
        return json(['code' => 200, 'msg' => 'success', 'data' => $data]);
    }

    /* 私信 */
    public function message(int $id): Response
    {
        $map1 = [
            ['from_user_id', '=', $this->userInfo['id']],
            ['to_user_id', '=', $id],
        ];
        $map2 = [
            ['from_user_id', '=', $id],
            ['to_user_id', '=', $this->userInfo['id']],
        ];
        $to_user = $this->getUserInfo($id);
        $message = Message::whereOr([$map1, $map2])->limit(50)->select()->toArray();
        return json(['code' => 200, 'msg' => 'success', 'data' => [
            'message' => $message,
            'user' => $to_user
        ]]);
    }

    /* 发送私信 */
    public function doMessage()
    {
        // 验证
        $post = $this->request->post();
        if ($post['id'] == $this->userInfo['id']) {
            $this->failure('无法给自己发送私信');
        }

        $this->transaction('发送', function () use ($post) {
            // 新增
            $data = [
                'from_user_id' => $this->userInfo['id'],
                'to_user_id' => $post['id'],
                'message' => $post['message'],
            ];
            $msg = Message::create($data);
            $data['id'] = $msg->id;
            $data['created_at'] = $msg->created_at;

            // 通知消息

            return $data;
        });
    }

    /* 上传横幅 */
    public function banner()
    {
        // 更新横幅
        $image = $this->image(810, 300);
        User::where('id', $this->userInfo['id'])->update(['banner' => $image]);
        $this->success(['url' => $image]);
    }

    /* 用户文章列表 */
    public function getArticle(): Response
    {
        $id = intval($this->request->input('id', 0));
        $where['user_id'] = $id;
        $data = $this->getArticleList($where);
        return json(['code' => 200, 'msg' => 'success', 'data' => $data]);
    }

}
