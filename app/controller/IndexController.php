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

    /* 个人中心 */
    public function account(Request $request)
    {
        return view('index/account');
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
