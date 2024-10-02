<?php

namespace plugin\webman\gateway;

use GatewayWorker\Lib\Gateway;

class Events
{
    public static function onWorkerStart($worker)
    {
        // ��ʼ��Redis�����ö�ʱ����
    }

    public static function onConnect($client_id)
    {
        //Gateway::sendToClient($client_id, "����IP��{$_SERVER['REMOTE_ADDR']}");
    }

    public static function onWebSocketConnect($client_id, $data)
    {
        if (empty($token = trim($data['get']['token']))) {
            Gateway::closeClient($client_id);
        }
        try {
            // ��֤token��������û���Ϣ����client_id
            $id = 10000;
            Gateway::bindUid($client_id, $id);
            self::output($client_id, '��¼�ɹ�');
        } catch (\Exception $e) {
            self::output($client_id, '��¼״̬�ѹ���', 10001);
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
        // ��uid���û�ɾ��session�����ú�̨Ϊ�˳�״̬
        Gateway::closeClient($client_id);
    }

    public static function output($client_id, $message, $code = 1)
    {
        $json = json_encode(compact('code', 'message'), 320);
        Gateway::sendToClient($client_id, $json);
    }

}
