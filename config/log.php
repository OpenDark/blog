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

return [
    'default' => [
        'handlers' => [
            [
                'class' => \support\log\MonologExtendHandler::class,
                'constructor' => [
                    null,
                    env('SINGLE_LOG_SIZE', 10485760),
                    Monolog\Logger::DEBUG,
                    true,
                    0755
                ],
                'formatter' => [
                    'class' => Monolog\Formatter\LineFormatter::class,
                    'constructor' => [null, 'Y-m-d H:i:s', true, true],
                ],
            ]
        ],
    ],
    'logger' => [
        'handlers' => [
            [
                'class' => \support\log\MonologExtendHandler::class,
                'constructor' => [
                    'debug',
                    env('SINGLE_LOG_SIZE', 10485760),
                    Monolog\Logger::DEBUG,
                    true,
                    0755
                ],
                'formatter' => [
                    'class' => Monolog\Formatter\LineFormatter::class,
                    'constructor' => [null, 'Y-m-d H:i:s', true, true],
                ],
            ]
        ],
    ],
    'email' => [
        'handlers' => [
            [
                'class' => \support\log\MonologExtendHandler::class,
                'constructor' => [
                    'email',
                    env('SINGLE_LOG_SIZE', 10485760),
                    Monolog\Logger::DEBUG,
                    true,
                    0755
                ],
                'formatter' => [
                    'class' => Monolog\Formatter\LineFormatter::class,
                    'constructor' => [null, 'Y-m-d H:i:s', true, true],
                ],
            ]
        ],
    ],
];
