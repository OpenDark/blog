<?php

use support\Request;
use Webman\Route;

Route::any('/common/captcha/{type}', [\app\controller\CommonController::class, 'captcha']);

Route::get('/post/user/{id:[1-9]\d*}', [\app\controller\UserController::class, 'getUser']);

Route::get('/category/{id:[1-9]\d*}', [\app\controller\CategoryController::class, 'index']);

Route::get('/post/{id:[1-9]\d*}', [\app\controller\PostController::class, 'index']);

Route::get('/user/{id:[1-9]\d*}', [\app\controller\UserController::class, 'index']);

Route::get('/draft', [\app\controller\AccountController::class, 'draft']);

Route::fallback(function (Request $request) {
    // ajax请求时返回json
    if ($request->expectsJson()) {
        return json(['code' => 404, 'msg' => '404 not found']);
    }
    // 页面请求返回404.html模版
    return view('404')->withStatus(404);
});

//Route::disableDefaultRoute();
