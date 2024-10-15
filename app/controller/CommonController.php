<?php

namespace app\controller;

use support\Request;
use support\Response;
use Webman\Captcha\CaptchaBuilder;
use Webman\Captcha\PhraseBuilder;
use app\model\User as UserModel;

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
    public function doRegister(): Response
    {
        return view('common/register');
    }

    /* 重置页面 */
    public function reset(): Response
    {
        return view('common/reset');
    }

    /* 重置页面 */
    public function doReset(): Response
    {
        return view('common/reset');
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

}
