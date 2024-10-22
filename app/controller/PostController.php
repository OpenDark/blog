<?php

namespace app\controller;

use support\Response;

class PostController extends BaseController
{
    protected bool|array $needLogin = [
        'comment',
        'favorite'
    ];

    /* 文章详情 */
    public function index(int $id): Response
    {
        return view('post/index');
    }

    /* 评论 */
    public function comment(): Response
    {
        return view('user/comment');
    }

    /* 收藏 */
    public function favorite(): Response
    {
        return view('user/favorite');
    }

    /* 文章搜索 */
    public function search(): Response
    {
        $s = $this->request->input('s', '');
        return view('post/search', [
            'title' => '搜索 - ' . $s,
            's' => $s,
        ]);
    }

    /* 搜索文章列表 */
    public function getArticle(): Response
    {
        $s = $this->request->input('s', '');
        $where[] = ['title', 'like', '%' . $s . '%'];
        $data = $this->getArticleList($where);
        return json(['code' => 200, 'msg' => 'success', 'data' => $data]);
    }

}
