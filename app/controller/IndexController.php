<?php

namespace app\controller;

use support\Request;
use support\view\ThinkPHP;

class IndexController
{
    public function index(Request $request)
    {
        return view('index/special', ['name' => 'webman']);
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
