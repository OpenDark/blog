<?php

namespace app\controller;

use app\model\Tag;
use app\model\User;
use app\model\Article;
use app\model\Message;
use app\model\EmailLog;
use app\model\ArticleTag;
use support\Response;
use think\db\Query;
use think\facade\Db;
use Webman\RedisQueue\Redis;

class AccountController extends BaseController
{
    protected bool|array $needLogin = true;

    /* 基本资料 */
    public function index(): Response
    {
        return view('account/index', [
            'title' => '基本资料',
            'user' => $this->getUserDetail(),
        ]);
    }

    /* 设置资料 */
    public function doSetting()
    {
        // 验证用户
        $post = $this->request->post();
        $user = User::findOrEmpty($this->userInfo['id']);
        $user->isEmpty() && $this->failure('用户不存在');
        empty($post['nickname']) && $post['nickname'] = $user['username'];
        empty($post['desc']) && $post['desc'] = '这个人很懒，什么都没有留下～';

        // 验证昵称
        if ($post['nickname'] != $user['nickname'] && $post['nickname'] != $user['username']) {
            $exist = User::where('nickname', $post['nickname'])->value('id');
            $exist && $this->failure('该昵称已存在');
        }

        // 更新信息
        $user->desc = $post['desc'];
        $user->nickname = $post['nickname'];
        $user->save() ? $this->success('保存成功') : $this->failure('保存设置失败');
    }

    /* 我的私信 */
    public function message(): Response
    {
        $message1 = Message::field('to_user_id as uid,MAX(id) as max')
            ->where('from_user_id', $this->userInfo['id'])
            ->group('to_user_id')->select()->toArray();

        $message2 = Message::field('from_user_id as uid,MAX(id) as max')
            ->where('to_user_id', $this->userInfo['id'])
            ->group('from_user_id')->select()->toArray();

        if (empty($message1) && empty($message2)) {
            $message = [];
        } elseif (empty($message1)) {
            $mid = array_column($message2, 'max');
            $message = Message::whereIn('id', $mid)->with([
                'from' => function (Query $query) {
                    $query->field('id,nickname,avatar');
                }
            ])->select()->toArray();
        } elseif (empty($message2)) {
            $mid = array_column($message1, 'max');
            $message = Message::whereIn('id', $mid)->with([
                'to' => function (Query $query) {
                    $query->field('id,nickname,avatar');
                }
            ])->select()->toArray();
        } else {
            $data = [];
            $all = array_merge($message1, $message2);
            foreach ($all as $val) {
                if (empty($data[$val['uid']]) || $data[$val['uid']] < $val['max']) {
                    $data[$val['uid']] = $val['max'];
                }
            }
            $mid = array_values($data);
            $message = Message::whereIn('id', $mid)->with([
                'from' => function (Query $query) {
                    $query->field('id,nickname,avatar');
                },
                'to' => function (Query $query) {
                    $query->field('id,nickname,avatar');
                }
            ])->select()->toArray();
        }
        foreach ($message as $key => $val) {
            if ($val['from_user_id'] == $this->userInfo['id']) {
                $val['user'] = $val['to'];
            } else {
                $val['user'] = $val['from'];
            }
            unset($val['from'], $val['to']);
            $message[$key] = $val;
        }

        return view('account/message', [
            'title' => '我的私信',
            'user' => $this->getUserDetail(),
            'message' => $message,
        ]);
    }

    /* 系统通知 */
    public function notice(): Response
    {
        return view('account/notice', [
            'title' => '系统通知',
            'user' => $this->getUserDetail(),
        ]);
    }

    /* 修改密码 */
    public function reset(): Response
    {
        return view('account/reset', [
            'title' => '修改密码',
            'user' => $this->getUserDetail(),
        ]);
    }

    /* 设置密码 */
    public function doReset()
    {
        // 验证用户
        $post = $this->request->post();
        $user = User::findOrEmpty($this->userInfo['id']);
        if ($user->isEmpty() || !check_password($post['oldpass'], $user['password'])) {
            $this->failure('帐号密码错误');
        }

        // 更新信息
        $user->password = create_password($post['password']);
        $this->request->session()->delete('userInfo'); // 退出登录
        $user->save() ? $this->success('修改成功') : $this->failure('修改密码失败');
    }

    /* 换绑邮箱 */
    public function email(): Response
    {
        return view('account/email', [
            'title' => '换绑邮箱',
            'user' => $this->getUserDetail(),
        ]);
    }

    /* 编辑邮箱 */
    public function doEmail(): void
    {
        // 验证账号密码
        $post = $this->request->post();
        $user = User::findOrEmpty($this->userInfo['id']);
        if ($user->isEmpty() || !check_password($post['password'], $user['password'])) {
            $this->failure('帐号密码错误');
        }

        // 验证邮箱与用户是否注册
        $exist = User::where('email', $post['email'])->value('id');
        $exist && $this->failure('该邮箱已注册');

        // 获取邮件发送记录
        $where = ['email' => $post['email'], 'type' => 'bind', 'code' => $post['email_code']];
        $log = EmailLog::field('id,validtime,used')->where($where)->findOrEmpty();

        // 验证邮箱验证码
        $log->isEmpty() && $this->failure('邮箱验证码错误');
        $log['used'] != 0 && $this->failure('邮箱验证码已使用');
        time() > $log['validtime'] && $this->failure('邮箱验证码已经超时，请重新获取');

        Db::startTrans();
        try {
            // 使用验证码
            $log['used'] = 1;
            $log->save();

            // 更新邮箱
            User::where('id', $this->userInfo['id'])
                ->update(['email' => $post['email']]);

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $msg = '系统错误：编辑邮箱失败';
            $this->logger($msg, $post, [], $e);
            $this->failure($msg);
        }
        $this->success('保存成功');
    }

    /* 发送邮箱验证码 */
    public function doEmailCode(): void
    {
        // 验证图形验证码
        $post = $this->request->post();
        if ($post['vercode'] !== session('blog-bind')) {
            $this->failure('图形验证码错误');
        }
        $this->request->session()->forget('blog-bind');

        // 验证邮箱是否注册
        $email = User::where('id', $this->userInfo['id'])->value('email');
        $email == strtolower($post['email']) && $this->failure('电子邮箱未改变');
        $exist = User::where('email', $post['email'])->value('id');
        $exist && $this->failure('该邮箱已注册');

        // 验证邮件发送记录
        $where = ['email' => $post['email'], 'type' => 'bind'];
        $log = EmailLog::field('count,sendtime')->where($where)->findOrEmpty();
        $time = time();
        $today = date('Y-m-d');
        $config = config('common.mail_limit');
        if (!$log->isEmpty()) {
            if (date('Y-m-d', $log['sendtime']) == $today && $log['count'] >= $config['max']) {
                $this->failure('今天发送次数已达上限。');
            }
            if ($time - $log['sendtime'] < $config['interval'] * 60) {
                $this->failure('每次发送需间隔' . $config['interval'] . '分钟，请稍后再试。');
            }
        }

        // 发送邮件
        $code = mt_rand(100000, 999999);
        $data = [
            'email' => $post['email'],
            'title' => '编辑邮箱',
            'body' => '通过邮箱编辑邮箱，验证码：' . $code . '，有效期：' . $config['valid'] . '分钟。',
        ];
        logger('【编辑邮箱】邮箱：' . $post['email'] . ' IP：' . $this->request->getRealIp(), $data, $data['title']);
        $res = Redis::send('send-email', $data);
        empty($res) && $this->failure('邮件发送失败。');

        // 写入记录
        if (!$log->isEmpty()) {
            $log['code'] = $code;
            $log['count'] += 1;
            $log['sendtime'] = $time;
            $log['validtime'] = $time + $config['valid'] * 60;
            $log['used'] = 0;
            // 当天第一次发送
            (date('Y-m-d', $log['sendtime']) != $today) && $log['count'] = 1;
            $result = $log->save();
        } else {
            $data = [
                'email' => $post['email'],
                'code' => $code,
                'type' => 'bind',
                'count' => 1,
                'sendtime' => $time,
                'validtime' => $time + $config['valid'] * 60,
            ];
            $result = EmailLog::create($data);
        }
        $success = '验证码已发送，请查收！';
        $result ? $this->success($success) : $this->failure('系统错误：邮箱验证码发送失败');
    }

    /* 投稿 */
    public function draft(): Response
    {
        return view('account/draft', [
            'title' => '投稿',
            'cate' => [1 => '工作', 2 => '学习', 3 => '生活'],
        ]);
    }

    /* 投稿 */
    public function doDraft()
    {
        // 文章标签（中文、字母、数字）
        $post = $this->request->post();
        $tag = str_replace([' ', '，'], ',', $post['tag']);
        $tag = preg_replace('/[^\p{Han}a-zA-Z0-9,]/u', '', $tag);
        $tags = [];
        if ($tag) {
            $tags = explode(',', trim($tag, ','));
            $tags = array_flip(array_flip($tags));
            $tags = array_slice($tags, 0, 5); // 只取5个标签
        }

        $this->transaction('投稿', function () use ($post, $tags) {
            $article = Article::create([
                'user_id' => $this->userInfo['id'],
                'category_id' => $post['category_id'],
                'title' => $post['title'],
                'summary' => $post['summary'],
                'content' => $post['content'],
                'thumbnail' => $post['thumbnail'] ?: '/static/img/none.jpg',
                'is_check' => $this->userInfo['id'] == 1 ? 1 : 0,
            ]);

            if ($tags) {
                // 更新标签
                $cur = Tag::whereIn('name', $tags)->column('id', 'name');
                $new = array_diff($tags, array_keys($cur));
                if ($new) {
                    $data = [];
                    foreach ($new as $val) {
                        $data[] = ['name' => $val];
                    }
                    $new_tag = (new Tag)->saveAll($data);
                    foreach ($new_tag as $val) {
                        $cur[$val['name']] = $val['id'];
                    }
                }

                // 新增文章标签
                $data = [];
                foreach ($cur as $val) {
                    $data[] = [
                        'article_id' => $article->id,
                        'tag_id' => $val,
                    ];
                }
                (new ArticleTag)->saveAll($data);
            }
        });
    }

    private function getUserDetail(): array
    {
        $user = $this->getUserInfo($this->userInfo['id']);
        empty($user) && $this->failure('用户不存在');
        return $user;
    }

    /* 上传头像 */
    public function avatar()
    {
        // 更新头像
        $image = $this->image();
        User::where('id', $this->userInfo['id'])->update(['avatar' => $image]);
        $this->success(['url' => $image]);
    }

    /* 投稿文章缩略图 */
    public function thumb()
    {
        $image = $this->image(480, 300);
        $this->success(['url' => $image]);
    }

    /* 投稿文章附图 */
    public function figure(): Response
    {
        $image = $this->image(800, 530);
        return json([
            'code' => 0,
            'msg' => 'success',
            'id' => 1,
            'data' => $image
        ]);
    }

}
