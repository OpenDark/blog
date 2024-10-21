<?php

namespace app\controller;

use app\model\Article;
use app\model\Link;
use app\model\Special;
use support\Response;
use support\View;

class SpecialController extends BaseController
{
    /* 首页 */
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

}
