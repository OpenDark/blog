<?php

namespace app\controller;

use support\Request;
use support\view\ThinkPHP;

class IndexController
{
    /* 首页 */
    public function index(Request $request)
    {
        return view('index/index');
    }

    /* 登录 */
    public function login(Request $request)
    {
        return view('index/login');
    }

    /* 注册 */
    public function reg(Request $request)
    {
        return view('index/reg');
    }

    /* 分类 */
    public function category(Request $request)
    {
        return view('index/category');
    }

    /* 分类 */
    public function special(Request $request)
    {
        return view('index/special');
    }

    /* 留言板 */
    public function guest(Request $request)
    {
        return view('index/guest');
    }

    /* 文章 */
    public function post(Request $request)
    {
        return view('index/post');
    }

    /* 搜索 */
    public function search(Request $request)
    {
        return view('index/search');
    }

    /* 标签归档 */
    public function tags(Request $request)
    {
        return view('index/tags');
    }

    /* 标签文章 */
    public function tag(Request $request)
    {
        return view('index/tag');
    }

    /* 投稿 */
    public function draft(Request $request)
    {
        return view('index/draft');
    }

    /* 用户 */
    public function user(Request $request)
    {
        return view('index/user');
    }

    /* 关注 */
    public function follow(Request $request)
    {
        return view('index/follow');
    }

    /* 评论 */
    public function comment(Request $request)
    {
        return view('index/comment');
    }

    /* 收藏 */
    public function favorite(Request $request)
    {
        return view('index/favorite');
    }

    /* 个人中心 */
    public function account(Request $request)
    {
        return view('index/account');
    }

    /* 我的私信 */
    public function message(Request $request)
    {
        return view('index/message');
    }

    /* 系统通知 */
    public function notice(Request $request)
    {
        return view('index/notice');
    }

    /* 修改密码 */
    public function reset(Request $request)
    {
        return view('index/reset');
    }

    public function view(Request $request)
    {
        return view('index/view', ['name' => 'webman']);
    }

    public function upload(Request $request)
    {
        $file = $request->file('file');
        if ($file && $file->isValid()) {
            $name = 'test.' . $file->getUploadExtension();
            $path = public_path('upload') . DIRECTORY_SEPARATOR . $name;
            $image = config('common.domain') . '/upload/' . $name;
            $file->move($path);
            return json([
                'code' => 0,
                'msg' => 'Upload Success',
                'id' => 1,
                'data' => $image
            ]);
        }
        return json(['code' => 1, 'msg' => 'File Not Found']);
    }

}
