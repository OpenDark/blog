<?php

namespace app\controller;

use support\Response;

class AccountController extends BaseController
{
    protected bool|array $needLogin = true;

    /* 个人中心 */
    public function index(): Response
    {
        return view('account/index');
    }

    /* 我的私信 */
    public function message(): Response
    {
        return view('account/message');
    }

    /* 系统通知 */
    public function notice(): Response
    {
        return view('account/notice');
    }

    /* 修改密码 */
    public function reset(): Response
    {
        return view('account/reset');
    }

    /* 投稿 */
    public function draft(): Response
    {
        return view('account/draft');
    }

}
