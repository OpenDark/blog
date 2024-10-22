<?php

namespace app\controller;

use think\facade\Db;
use support\Request;
use support\Response;
use Webman\RedisQueue\Redis;
use Webman\Captcha\CaptchaBuilder;
use Webman\Captcha\PhraseBuilder;
use app\model\User as UserModel;
use app\model\EmailLog as EmailLogModel;

class CommonController extends BaseController
{
    /* 登录页面 */
    public function login(): Response
    {
        // 已登录则重定向首页
        $url = $this->request->get('url', '/');
        if (!empty($this->userInfo)) {
            return redirect($url);
        }
        return view('common/login', [
            'title' => '账号登录',
            'url' => $url,
        ]);
    }

    /* 登录 */
    public function doLogin(): void
    {
        // 验证码
        $post = $this->request->post();
        if ($post['vercode'] !== session('blog-login')) {
            $this->failure('验证码错误');
        }
        $this->request->session()->forget('blog-login');

        // 验证用户
        $user = UserModel::where('username', $post['username'])->findOrEmpty();
        if ($user->isEmpty() || !check_password($post['password'], $user['password'])) {
            $this->failure('帐号或密码错误');
        }
        if ($user->status != 0) {
            $this->failure('当前用户暂时无法登录');
        }

        // 更新用户
        $user['last_time'] = date('Y-m-d H:i:s');
        $user['last_ip'] = $this->request->getRealIp();
        $res = $user->save();
        !$res && $this->failure('登陆失败');

        // 设置SESSION
        $info = [
            'id' => $user['id'],
            'username' => $user['username'],
            'nickname' => $user['nickname'],
            'avatar' => $user['avatar'],
        ];
        $this->request->session()->set('userInfo', $info);
        $this->success($info);
    }

    /* 注册页面 */
    public function register(): Response
    {
        return view('common/register');
    }

    /* 注册 */
    public function doRegister()
    {
        // 验证邮箱与用户是否注册
        $post = $this->request->post();
        $exist = UserModel::where('email', $post['email'])->value('id');
        $exist && $this->failure('该邮箱已注册');
        $exist = UserModel::where('username', $post['username'])->value('id');
        $exist && $this->failure('该用户名已存在');

        // 获取邮件发送记录
        $where = ['email' => $post['email'], 'type' => 'register', 'code' => $post['email_code']];
        $log = EmailLogModel::field('id,validtime,used')->where($where)->findOrEmpty();

        // 验证邮箱验证码
        $log->isEmpty() && $this->failure('邮箱验证码错误');
        $log['used'] != 0 && $this->failure('邮箱验证码已使用');
        time() > $log['validtime'] && $this->failure('邮箱验证码已经超时，请重新获取');

        Db::startTrans();
        try {
            // 使用验证码
            $log['used'] = 1;
            $log->save();

            // 新增账号
            $imgcdn = config('common.imgcdn');
            $data['money'] = 0;
            $data['email'] = $post['email'];
            $data['username'] = $post['username'];
            $data['nickname'] = $post['username'];
            $data['password'] = create_password($post['password']);
            $data['avatar'] = $imgcdn . mt_rand(1, 18) . '.png';
            $data['banner'] = $imgcdn . 'banner.jpg';
            $data['desc'] = '这个人很懒，什么都没有留下～';
            $data['join_ip'] = $data['last_ip'] = $this->request->getRealIp();
            $data['join_time'] = $data['last_time'] = date('Y-m-d H:i:s');
            $data['birthday'] = date('Y-m-d');
            UserModel::create($data);

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $msg = '系统错误：账号注册失败';
            $this->logger($msg, $post, [], $e);
            $this->failure($msg);
        }
        $this->success('注册成功');
    }

    /* 发送邮箱验证码 */
    public function doRegisterCode(): void
    {
        // 验证图形验证码
        $post = $this->request->post();
        if ($post['vercode'] !== session('blog-register')) {
            $this->failure('图形验证码错误');
        }
        $this->request->session()->forget('blog-register');

        // 验证邮箱是否注册
        $exist = UserModel::where('email', $post['email'])->value('id');
        $exist && $this->failure('该邮箱已注册');

        // 验证邮件发送记录
        $where = ['email' => $post['email'], 'type' => 'register'];
        $log = EmailLogModel::field('count,sendtime')->where($where)->findOrEmpty();
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
            'title' => '注册账号',
            'body' => '通过邮箱注册账号，验证码：' . $code . '，有效期：' . $config['valid'] . '分钟。',
        ];
        logger('【注册账号】邮箱：' . $post['email'] . ' IP：' . $this->request->getRealIp(), $data, $data['title']);
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
                'type' => 'register',
                'count' => 1,
                'sendtime' => $time,
                'validtime' => $time + $config['valid'] * 60,
            ];
            $result = EmailLogModel::create($data);
        }
        $success = '验证码已发送，请查收！';
        $result ? $this->success($success) : $this->failure('系统错误：邮箱验证码发送失败');
    }

    /* 重置页面 */
    public function reset(): Response
    {
        return view('common/reset');
    }

    /* 重置页面 */
    public function doReset(): void
    {
        // 验证邮箱是否注册
        $post = $this->request->post();
        $user = UserModel::field('id')->where('email', $post['email'])->findOrEmpty();
        $user->isEmpty() && $this->failure('该邮箱未注册');

        // 获取邮件发送记录
        $where = ['email' => $post['email'], 'type' => 'reset', 'code' => $post['email_code']];
        $log = EmailLogModel::field('id,validtime,used')->where($where)->findOrEmpty();

        // 验证邮箱验证码
        $log->isEmpty() && $this->failure('邮箱验证码错误');
        $log['used'] != 0 && $this->failure('邮箱验证码已使用');
        time() > $log['validtime'] && $this->failure('邮箱验证码已经超时，请重新获取');

        Db::startTrans();
        try {
            // 使用验证码
            $log['used'] = 1;
            $log->save();

            // 更新密码
            $user['password'] = create_password($post['password']);
            $user->save();

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $msg = '系统错误：重置密码失败';
            $this->logger($msg, $post, [], $e);
            $this->failure($msg);
        }
        $this->success('重置成功');
    }

    /* 发送邮箱验证码 */
    public function doResetCode(): void
    {
        // 验证图形验证码
        $post = $this->request->post();
        if ($post['vercode'] !== session('blog-reset')) {
            $this->failure('图形验证码错误');
        }
        $this->request->session()->forget('blog-reset');

        // 验证邮箱是否注册
        $exist = UserModel::where('email', $post['email'])->value('id');
        !$exist && $this->failure('该邮箱未注册');

        // 验证邮件发送记录
        $where = ['email' => $post['email'], 'type' => 'reset'];
        $log = EmailLogModel::field('count,sendtime')->where($where)->findOrEmpty();
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
            'title' => '忘记密码',
            'body' => '通过邮箱重置密码，验证码：' . $code . '，有效期：' . $config['valid'] . '分钟。',
        ];
        logger('【注册账号】邮箱：' . $post['email'] . ' IP：' . $this->request->getRealIp(), $data, $data['title']);
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
                'type' => 'reset',
                'count' => 1,
                'sendtime' => $time,
                'validtime' => $time + $config['valid'] * 60,
            ];
            $result = EmailLogModel::create($data);
        }
        $success = '验证码已发送，请查收！';
        $result ? $this->success($success) : $this->failure('系统错误：邮箱验证码发送失败');
    }

    /**
     * 退出
     */
    public function logout(): Response
    {
        $url = $this->request->get('url', '/');
        $this->request->session()->delete('userInfo');
        return redirect($url);
    }

    /**
     * 验证码
     * @param Request $request
     * @param string $type
     * @return Response
     */
    public function captcha(Request $request, string $type = 'login'): Response
    {
        $builder = new PhraseBuilder(4, '0123456789');
        $captcha = new CaptchaBuilder(null, $builder);
        $captcha->build(120, 38);
        $code = strtolower($captcha->getPhrase());
        $request->session()->set("blog-$type", $code);
        $img_content = $captcha->get();
        return response($img_content, 200, ['Content-Type' => 'image/jpeg']);
    }

}
