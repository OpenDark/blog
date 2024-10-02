<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

use Webman\Session\FileSessionHandler;
use Webman\Session\RedisSessionHandler;
use Webman\Session\RedisClusterSessionHandler;

return [

    'type' => env('SESSION_DRIVER', 'file'), // or redis or redis_cluster

    'handler' => env('SESSION_DRIVER', 'file') == 'file' ? FileSessionHandler::class :
        (env('SESSION_DRIVER', 'file') == 'redis' ? RedisSessionHandler::class : RedisClusterSessionHandler::class),

    'config' => [
        'file' => [
            'save_path' => runtime_path() . '/sessions',
        ],
        'redis' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => env('REDIS_PORT', 6379),
            'auth' => '',
            'timeout' => env('SESSION_TIMEOUT', 2),
            'database' => env('SESSION_DATABASE', 0),
            'prefix' => env('SESSION_PREFIX', 'session:'),
        ],
        'redis_cluster' => [
            'host' => ['127.0.0.1:7000', '127.0.0.1:7001', '127.0.0.1:7001'],
            'timeout' => env('SESSION_TIMEOUT', 2),
            'auth' => '',
            'prefix' => env('SESSION_PREFIX', 'session:'),
        ]
    ],

    'session_name' => env('SESSION_NAME', 'PHPSESSID'),
    
    'auto_update_timestamp' => false,

    'lifetime' => 7*24*60*60,

    'cookie_lifetime' => 365*24*60*60,

    'cookie_path' => '/',

    'domain' => '',
    
    'http_only' => true,

    'secure' => false,
    
    'same_site' => '',

    'gc_probability' => [1, 1000],

];
