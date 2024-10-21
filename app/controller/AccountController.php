<?php

namespace app\controller;

use app\model\Article;
use app\model\Link;
use app\model\Special;
use support\Response;
use support\View;

class AccountController extends BaseController
{
    /* 个人中心 */
    public function index(): Response
    {
        // 轮播
        $this->active['is_carousel'] = 1;
        $carousel = Article::field('id,title,thumbnail')
            ->where($this->active)
            ->order('id desc')
            ->select()->toArray();

        // 专题
        $special = Special::field('id,name,thumbnail')
            ->limit(4)->select()->toArray();

        // 友链
        $link = Link::field('id,title,url')->select()->toArray();

        View::assign([
            'special'=> $special,
            'carousel' => $carousel,
            'link'=> $link,
        ]);
        return view('index/index');
    }

    /* 个人中心 */
    public function account(): Response
    {
        return view('index/account');
    }

    /* 我的私信 */
    public function message(): Response
    {
        return view('index/message');
    }

    /* 系统通知 */
    public function notice(): Response
    {
        return view('index/notice');
    }

    /* 修改密码 */
    public function reset(): Response
    {
        return view('index/reset');
    }

}
