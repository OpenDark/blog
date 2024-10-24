<?php

namespace app\controller;

use support\Response;

class TagsController extends BaseController
{
    /* 首页 */
    public function index(): Response
    {
        return view('tags/index', [
            'title' => '标签归档',
            'active' => 'tags',
        ]);
    }

    /* 标签 */
    public function tag(int $id): Response
    {

    }

}
