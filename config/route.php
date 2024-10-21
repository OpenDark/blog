<?php

use support\Request;
use Webman\Route;

Route::any('/common/captcha/{type}', [\app\controller\CommonController::class, 'captcha']);

Route::get('/post/user/{id:\d+}', [\app\controller\UserController::class, 'getUser']);

Route::get('/category/{id:\d+}', [\app\controller\CategoryController::class, 'index']);

Route::get('/post/{id:\d+}', [\app\controller\PostController::class, 'index']);

Route::fallback(function (Request $request) {
    // ajax请求时返回json
    if ($request->expectsJson()) {
        return json(['code' => 404, 'msg' => '404 not found']);
    }
    // 页面请求返回404.html模版
    return view('404')->withStatus(404);
});

//Route::disableDefaultRoute();
