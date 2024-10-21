<?php

namespace app\controller;

use app\model\Article;
use app\model\Special;
use support\Response;

class SpecialController extends BaseController
{
    /* 首页 */
    public function index(): Response
    {
        return view('special/index', [
            'title' => '专题列表',
            'active' => 'special',
        ]);
    }

}
