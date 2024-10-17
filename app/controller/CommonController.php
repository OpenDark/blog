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
        if (!empty($this->request->userInfo)) {
            return redirect($url);
        }
        return view('common/login');
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
        $this->success('注册成功');
    }

    /* 发送邮箱验证码 */
    public function doRegisterCode()
    {
        $this->success('发送成功');
    }

    /* 重置页面 */
    public function reset(): Response
    {
        return view('common/reset');
    }

    /* 重置页面 */
    public function doReset()
    {
        $this->success('重置成功');
    }

    /* 发送邮箱验证码 */
    public function doResetCode()
    {
        $this->success('发送成功');
    }

    /**
     * 退出
     */
    public function logout(): void
    {
        $this->request->session()->delete('userInfo');
        $this->success();
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

    /**
     * 注册账号
     */
    public function register1()
    {
        // 前端验证注册上限
        $max = config('game.max_login');
        $u = $this->param['u'] ?? 0;
        $u > $max && $this->failure('注册个数已达上限');

        // IP限制注册上限
        $count = UserModel::where('reg_ip', $this->ip)->count();
        $count >= $max && $this->failure('注册个数已达上限');

        // 验证账号是否存在
        $exist = UserModel::where('username', $this->param['username'])->value('id');
        $exist && $this->failure('账号已存在');

        // 验证邮箱是否使用
        $exist = UserModel::where('email', $this->param['email'])->value('id');
        $exist && $this->failure('邮箱已使用');

        Db::startTrans();
        try {
            // 新增账号
            $data['username'] = $this->param['username'];
            $data['password'] = create_password($this->param['password']);
            $data['email'] = $this->param['email'];
            $data['reg_ip'] = $this->ip;
            $data['reg_time'] = time();
            $account = UserModel::create($data);
            empty($account->id) && $this->failure('注册失败');

            // 注册推广
            $t = $this->param['t'] ?? 0;
            if ($t > 0) {
                // 验证推广账号是否存在
                $exist = UserModel::where('id', $t)->value('id');
                if ($exist) {
                    // 添加推广记录
                    TgModel::create([
                        'account_id' => $account->id,
                        'invite_id' => $t,
                    ]);
                }
            }

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $msg = '系统错误：账号注册失败';
            $this->logger($msg, $this->param, [], $e);
            $this->failure($msg);
        }
        $this->success('注册成功');
    }

    /**
     * 修改密码
     */
    public function xiugai()
    {
        // 验证账号
        $account = UserModel::where([
            'username' => $this->param['username'],
            'password' => create_password($this->param['password']),
        ])->find();
        empty($account) && $this->failure('帐号或密码错误');

        // 更新密码
        $newpassword = create_password($this->param['newpassword']);
        if ($newpassword == $account['password']) {
            $this->success('修改成功');
        }
        $account['password'] = $newpassword;
        $res = $account->save();
        $res ? $this->success('修改成功') : $this->failure('修改失败');
    }

    /**
     * 获取邮箱验证码
     */
    public function getcode()
    {
        // 获取账号
        $where = ['username' => $this->param['username'], 'email' => $this->param['mail']];
        $account = UserModel::field('id,status')->where($where)->find();
        empty($account) && $this->failure('账号或邮箱错误。');
        $account['status'] == 2 && $this->failure('账号被封。');

        // 验证邮件发送记录
        $where = ['account_id' => $account['id'], 'type' => 'find'];
        $log = EmailLogModel::field('count,sendtime')->where($where)->find();
        $time = time();
        $today = date('Y-m-d');
        $config = config('game.mail_limit');
        if ($log) {
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
            'email' => $this->param['mail'],
            'title' => '密码找回',
            'body' => '通过邮箱找回密码，验证码：' . $code . '，有效期：' . $config['valid'] . '分钟。',
        ];
        logger('【密码找回】账号：' . $account['id'] . ' IP：' . $this->ip, $data, $data['title']);
        $res = Redis::send('send-email', $data);
        empty($res) && $this->failure('邮件发送失败。');

        // 写入记录
        if ($log) {
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
                'account_id' => $account['id'],
                'code' => $code,
                'type' => 'find',
                'count' => 1,
                'sendtime' => $time,
                'validtime' => $time + $config['valid'] * 60,
            ];
            $result = EmailLogModel::create($data);
        }
        $success = '验证码已经发送到你的邮箱，请查看邮箱的验证码<br>请确保你邮箱没有设置邮件拦截，以免被拦截无法收到！';
        $result ? $this->success($success) : $this->failure('系统错误：邮箱验证码获取失败');
    }

    /**
     * 邮箱找回密码
     */
    public function zhaohui()
    {
        // 获取账号
        $where = ['username' => $this->param['username']];
        $account = UserModel::field('id,status,password')->where($where)->find();
        empty($account) && $this->failure('账号或邮箱错误。');
        $account['status'] == 2 && $this->failure('账号被封。');

        // 获取邮件发送记录
        $where = ['account_id' => $account['id'], 'type' => 'find', 'code' => $this->param['code']];
        $log = EmailLogModel::field('id,validtime,used')->where($where)->find();

        // 验证有效
        empty($log) && $this->failure('验证码错误。');
        $log['used'] != 0 && $this->failure('验证码已使用。');
        time() > $log['validtime'] && $this->failure('验证码已经超时，请重新获取。');

        $this->transaction('密码找回', function () use ($log, $account) {
            // 使用验证码
            $log['used'] = 1;
            $res = $log->save();
            if (!$res) {
                throw new \Exception('验证码核验失败。');
            }

            // 更新密码
            $password = create_password($this->param['password']);
            if ($account['password'] != $password) {
                $account['password'] = $password;
                $res = $account->save();
                if (!$res) {
                    throw new \Exception('密码更新失败。');
                }
            }
        });
    }

}
