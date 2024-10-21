<?php

namespace app\controller;

use support\Response;

class PostController extends BaseController
{
    /* 文章详情 */
    public function index(): Response
    {
        return view('post/index');
    }

}
