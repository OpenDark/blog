<?php

namespace plugin\webman\gateway;

use GatewayWorker\Lib\Gateway;

class Events
{
    public static function onWorkerStart($worker)
    {
        // 初始化Redis，设置定时器等
    }

    public static function onConnect($client_id)
    {
        //Gateway::sendToClient($client_id, "连接IP：{$_SERVER['REMOTE_ADDR']}");
    }

    public static function onWebSocketConnect($client_id, $data)
    {
        if (empty($token = trim($data['get']['token']))) {
            Gateway::closeClient($client_id);
        }
        try {
            // 验证token解析获得用户信息，绑定client_id
            $id = 10000;
            Gateway::bindUid($client_id, $id);
            self::output($client_id, '登录成功');
        } catch (\Exception $e) {
            self::output($client_id, '登录状态已过期', 10001);
            Gateway::closeClient($client_id);
        }
    }

    public static function onMessage($client_id, $message)
    {
        if ($message == 'session') {
            Gateway::sendToClient($client_id, json_encode($_SESSION, 320));
        }
    }

    public static function onClose($client_id)
    {
        // 绑定uid的用户删除session，设置后台为退出状态
        Gateway::closeClient($client_id);
    }

    public static function output($client_id, $message, $code = 1)
    {
        $json = json_encode(compact('code', 'message'), 320);
        Gateway::sendToClient($client_id, $json);
    }

}
