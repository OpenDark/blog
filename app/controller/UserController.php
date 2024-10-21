<?php

namespace app\controller;

use support\Response;

class UserController extends BaseController
{
    /* 博客用户 */
    public function getUser(int $id): Response
    {
        $group = ['管理团队', '普通用户', '认证作者'];
        $data['user'] = $this->getUserInfo($id);
        empty($data['user']) && $data['user']['role'] = 1;
        $data['user']['group'] = $group[$data['user']['role']];
        $data['article'] = $this->getArticleLatest($id);
        return json(['code' => 200, 'msg' => 'success', 'data' => $data]);
    }

    /* 用户 */
    public function index(int $id): Response
    {
        return view('user/index');
    }

    /* 关注 */
    public function follow(): Response
    {
        return view('user/follow');
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

}
