<?php
return  [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'driver'      => env('DBTYPE', 'mysql'),
            'host'        => env('HOSTNAME', '127.0.0.1'),
            'port'        => env('HOSTPORT', '3306'),
            'database'    => env('DATABASE', 'dbname'),
            'username'    => env('USERNAME', 'root'),
            'password'    => env('PASSWORD', 'root'),
            'charset'     => env('CHARSET', 'utf8mb4'),
            'collation'   => env('COLLATION', 'utf8mb4_general_ci'),
            'prefix'      => env('PREFIX', ''),
            'strict'      => true,
            'engine'      => null,
        ],
    ],
];