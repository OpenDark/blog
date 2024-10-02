<?php

use Webman\GatewayWorker\Gateway;
use Webman\GatewayWorker\BusinessWorker;
use Webman\GatewayWorker\Register;

return [
    'gateway' => [
        'handler'     => Gateway::class,
        'listen'      => 'websocket://0.0.0.0:' . env('GATEWAY_WEBSOCKET_PORT', 7272),
        'count'       => cpu_count(),
        'reloadable'  => false,
        'constructor' => ['config' => [
            'lanIp'           => '127.0.0.1',
            'startPort'       => env('GATEWAY_PORT', 2300),
            'pingInterval'    => 25,
            'pingData'        => '{"type":"ping"}',
            'registerAddress' => '127.0.0.1:' . env('GATEWAY_TEXT_PORT', 1236),
            'onConnect'       => function(){},
        ]]
    ],
    'worker' => [
        'handler'     => BusinessWorker::class,
        'count'       => cpu_count()*2,
        'constructor' => ['config' => [
            'eventHandler'    => plugin\webman\gateway\Events::class,
            'name'            => env('GATEWAY_WORKER_NAME', 'ChatBusinessWorker'),
            'registerAddress' => '127.0.0.1:' . env('GATEWAY_TEXT_PORT', 1236),
        ]]
    ],
    'register' => [
        'handler'     => Register::class,
        'listen'      => 'text://127.0.0.1:' . env('GATEWAY_TEXT_PORT', 1236),
        'count'       => 1, // Must be 1
        'constructor' => []
    ],
];
