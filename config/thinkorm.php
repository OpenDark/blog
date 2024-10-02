<?php

return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            // 数据库类型
            'type' => env('DBTYPE', 'mysql'),
            // 服务器地址
            'hostname' => env('HOSTNAME', '127.0.0.1'),
            // 数据库名
            'database' => env('DATABASE', 'dbname'),
            // 数据库用户名
            'username' => env('USERNAME', 'root'),
            // 数据库密码
            'password' => env('PASSWORD', 'root'),
            // 数据库连接端口
            'hostport' => env('HOSTPORT', '3306'),
            // 数据库连接参数
            'params' => [
                // 连接超时3秒
                \PDO::ATTR_TIMEOUT => env('ATTR_TIMEOUT', 3),
            ],
            // 数据库编码默认采用utf8
            'charset' => env('CHARSET', 'utf8mb4'),
            // 数据库表前缀
            'prefix' => env('PREFIX', ''),
            // 断线重连
            'break_reconnect' => true,
            // 关闭SQL监听日志
            'trigger_sql' => true,
            // 是否开启字段缓存
            'fields_cache' => !env('APP_DEBUG', false),
            // 自定义分页类
            'bootstrap' =>  ''
        ],
    ],
];
