<?php

namespace app\controller;

use support\Response;

class UserController extends BaseController
{
    /* 博客用户 */
    public function getUser(int $id): Response
    {
        $id = $id ?: 1;
        $group = ['管理团队', '普通用户', '认证作者'];
        $data['user'] = $this->getUserInfo($id);
        empty($data['user']) && $data['user']['role'] = 1;
        $data['user']['group'] = $group[$data['user']['role']];
        $data['article'] = $this->getArticleLatest($id);
        return json(['code' => 200, 'msg' => 'success', 'data' => $data]);
    }

    /**
     * 不需要登录的方法
     */
    protected $noNeedLogin = ['login'];

    /* 投稿 */
    public function draft(): Response
    {
        return view('index/draft');
    }

    /* 用户 */
    public function user(): Response
    {
        return view('index/user');
    }

    /* 关注 */
    public function follow(): Response
    {
        return view('index/follow');
    }

    /* 评论 */
    public function comment(): Response
    {
        return view('index/comment');
    }

    /* 收藏 */
    public function favorite(): Response
    {
        return view('index/favorite');
    }

}
