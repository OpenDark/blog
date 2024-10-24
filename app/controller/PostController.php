<?php

namespace app\controller;

use support\Response;
use think\db\Query;
use app\model\Article;
use app\model\Comment;
use app\model\Favorite;
use app\model\ArticleTag;
use app\model\Like;
use app\model\Tag;

class PostController extends BaseController
{
    protected bool|array $needLogin = [
        'doComment',
        'doFavorite',
        'doLike',
    ];

    /* 文章详情 */
    public function index(int $id): Response
    {
        // 文章详情
        $field = 'id,user_id,category_id,special_id,title,summary,content,thumbnail,';
        $field .= 'read_num,reply_num,like_num,favorite_num,created_at';
        $data = Article::field($field)->where($this->active)->where('id', $id)->with([
            'user' => function (Query $query) {
                $query->field('id,nickname,avatar');
            },
            'category' => function (Query $query) {
                $query->field('id,name');
            },
            'special' => function (Query $query) {
                $query->field('id,name');
            }
        ])->findOrEmpty()->toArray();
        empty($data) && $this->failure('文章不存在');
        $data['read_num'] = formatNum($data['read_num']);
        $data['reply_num'] = formatNum($data['reply_num']);
        $data['like_num'] = formatNum($data['like_num']);
        $data['favorite_num'] = formatNum($data['favorite_num']);
        $data['created_at'] = date('Y年m月d日 H:i', strtotime($data['created_at']));

        // 标签
        $data['tags'] = [];
        $tag = ArticleTag::where('article_id', $id)->column('tag_id');
        if ($tag) {
            $tags = Tag::column('name', 'id');
            foreach ($tag as $tid) {
                isset($tags[$tid]) && $data['tags'][] = [
                    'id' => $tid,
                    'name' => $tags[$tid],
                ];
            }
        }

        // 收藏
        $data['favorite'] = 0;
        if ($this->userInfo) {
            $favorite = Favorite::where([
                'user_id' => $this->userInfo['id'],
                'article_id' => $id,
            ])->findOrEmpty();
            if (!$favorite->isEmpty() && $favorite['is_cancel'] == 0) {
                $data['favorite'] = 1;
            }
        }

        // 上一篇文章
        $def = [
            'id' => $data['id'],
            'title' => $data['title'],
            'thumbnail' => $data['thumbnail'],
            'created_at' => $data['created_at'],
        ];
        $field = 'id,title,thumbnail,created_at';
        $prev = Article::field($field)->where($this->active)
            ->where('id', '<', $id)->order('id desc')->findOrEmpty()->toArray();
        empty($prev) && $prev = $def;
        $data['prev'] = $prev;

        // 下一篇文章
        $next = Article::field($field)->where($this->active)
            ->where('id', '>', $id)->order('id asc')->findOrEmpty()->toArray();
        empty($next) && $next = $def;
        $data['next'] = $next;

        // 更新文章阅读数
        Article::where('id', $id)
            ->inc('read_num')->update();

        return view('post/index', [
            'title' => $data['title'],
            'active' => 'category' . $data['category_id'],
            'uid' => strval($data['user_id']),
            'domain' => $this->domain,
            'post' => $data,
            'prev' => $prev,
            'next' => $next,
        ]);
    }

    /* 文章评论 */
    public function getComment(int $id): Response
    {
        $names = [];
        $page = intval($this->request->input('page', 1));
        $field = 'id,user_id,content,pid,reply_id,agree_num,oppose_num,is_hide,created_at';
        $comment = Comment::field($field)
            ->where('reply_id', 0)->where('article_id', $id)
            ->withJoin(['user' => ['id', 'nickname', 'avatar']], 'LEFT')
            ->order('id desc')->paginate([
                'page' => $page,
                'list_rows' => 10,
                'var_page' => 'page',
            ])->toArray();
        foreach ($comment['data'] as $key => $val) {
            $names[$val['id']] = $val['user']['nickname'] ?? '未知';
            $val['is_hide'] && $comment['data'][$key]['content'] = '该留言已被屏蔽';
        }

        // 回复消息
        if ($comment['data']) {
            $ids = array_column($comment['data'], 'id');
            $reply = Comment::field($field)->whereIn('reply_id', $ids)
                ->withJoin(['user' => ['id', 'nickname', 'avatar']], 'LEFT')
                ->select()->toArray();
            if ($reply) {
                $data = [];
                foreach ($reply as $val) {
                    $names[$val['id']] = $val['user']['nickname'] ?? '未知';
                }
                foreach ($reply as $val) {
                    $val['is_hide'] && $val['content'] = '该留言已被屏蔽';
                    $val['to_user'] = '@' . $names[$val['pid']];
                    $data[$val['reply_id']][] = $val;
                }
                if ($data) {
                    foreach ($comment['data'] as $key => $val) {
                        $comment['data'][$key]['reply'] = $data[$val['id']] ?? [];
                    }
                }
            }
        }

        return json(['code' => 200, 'msg' => 'success', 'data' => $comment]);
    }

    /* 文章评论 */
    public function doComment()
    {
        $post = $this->request->post();
        $data = [
            'user_id' => $this->userInfo['id'],
            'article_id' => $post['id'],
            'content' => $post['content'],
            'ipaddr' => $this->request->getRealIp(),
            'reply_id' => $post['rid'],
            'pid' => $post['pid'],
        ];
        $this->transaction('评论', function () use ($data) {
            // 新增评论
            Comment::create($data);

            // 更新文章回复数
            Article::where('id', $data['article_id'])
                ->inc('reply_num')->update();
        });
    }

    /* 收藏 */
    public function doLike()
    {
        $post = $this->request->post();
        $where = [
            'user_id' => $this->userInfo['id'],
            'article_id' => $post['id'],
        ];
        $like = Like::where($where)->findOrEmpty();
        !$like->isEmpty() && $this->failure('已点赞');

        $this->transaction('点赞', function () use ($where) {
            // 点赞
            Like::create($where);

            // 更新文章点赞数
            Article::where('id', $where['article_id'])
                ->inc('like_num')->update();
        });
    }

    /* 收藏 */
    public function doFavorite()
    {
        $post = $this->request->post();
        $where = [
            'user_id' => $this->userInfo['id'],
            'article_id' => $post['id'],
        ];
        $favorite = Favorite::where($where)->findOrEmpty();

        $is_cancel = 0;
        if (!$favorite->isEmpty()) {
            $is_cancel = 1 - $favorite['is_cancel'];
        }
        $act = $is_cancel ? '取消收藏' : '收藏';

        $this->transaction($act, function () use ($favorite, $is_cancel, $where) {
            // 收藏或取消
            if ($favorite->isEmpty()) {
                Favorite::create($where);
            } else {
                $favorite['is_cancel'] = $is_cancel;
                $favorite->save();
            }

            // 更新文章收藏数
            Article::where('id', $where['article_id'])
                ->inc('favorite_num', $is_cancel)->update();
        });
    }

    /* 文章搜索 */
    public function search(): Response
    {
        $s = $this->request->input('s', '');
        return view('post/search', [
            'title' => '搜索 - ' . $s,
            's' => $s,
        ]);
    }

    /* 搜索文章列表 */
    public function getArticle(): Response
    {
        $s = $this->request->input('s', '');
        $where[] = ['title', 'like', '%' . $s . '%'];
        $data = $this->getArticleList($where);
        return json(['code' => 200, 'msg' => 'success', 'data' => $data]);
    }

}
