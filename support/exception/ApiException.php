<?php

namespace support\exception;

use support\exception\BusinessException;
use Webman\Http\Request;
use Webman\Http\Response;

class ApiException extends BusinessException
{
    public function render(Request $request): ?Response
    {
        $msg = $json = $this->getMessage();
        $data = json_decode($json, true);
        $is_array = false;
        if (!empty($data)) {
            $is_array = true;
            $msg = $data['msg'];
        }
        if ($request->expectsJson()) {
            if ($is_array) {
                $response = response($json)->header('Content-Type', 'application/json');
            } else {
                $data = ['code' => 400, 'msg' => $msg];
                $response = response(json_encode($data, 320));
            }
            return $response;
        }
        if ($this->getCode() == 403) {
            return redirect('/public/login');
        } else {
            return view('error', ['error' => $msg ?? '系统错误'])->withStatus(404);
        }
    }
}
