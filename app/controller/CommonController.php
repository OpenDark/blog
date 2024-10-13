<?php

namespace app\controller;

use support\Request;
use support\Response;
use Webman\Captcha\CaptchaBuilder;
use Webman\Captcha\PhraseBuilder;
use support\exception\ApiException;
use app\model\User as UserModel;

class CommonController extends BaseController
{
    /* 登录 */
    public function login(): Response
    {
        // 已登录则重定向首页
        $url = $this->request->get('url', '/');
        if (!empty($this->request->userInfo)) {
            return redirect($url);
        }
        return view('public/login');
    }

    /* 登录 */
    public function doLogin(): Response
    {
//        if ($this->request->method() !== 'POST') {
//            return view('404')->withStatus(404);
//        }

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
        $this->request->session()->set('userInfo', $user['id']);
        $this->success([
            'id' => $user['id'],
            'username' => $user['username'],
            'nickname' => $user['nickname'],
            'avatar' => $user['avatar'],
        ]);
    }

    /* 注册 */
    public function register(Request $request): Response
    {
        return view('public/register');
    }

    /**
     * 退出
     * @return Response
     * @throws ApiException
     */
    public function logout(): Response
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
