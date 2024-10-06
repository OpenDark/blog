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

//use support\view\Raw;
//use support\view\Twig;
//use support\view\Blade;
use support\view\ThinkPHP;

return [
    'handler' => ThinkPHP::class,
    'options' => [
        // 模板后缀
        'view_suffix' => 'html',
        // 模板文件名分隔符
        'view_depr' => DIRECTORY_SEPARATOR,
        // 模板引擎普通标签开始标记
        'tpl_begin' => '{',
        // 模板引擎普通标签结束标记
        'tpl_end' => '}',
        // 标签库标签开始标记
        'taglib_begin' => '{',
        // 标签库标签结束标记
        'taglib_end' => '}',
        // 模板输出替换
        'tpl_replace_string' => [
            '__STATIC__' => '/static',
            '__JS__' => '/static/js',
            '__CSS__' => '/static/css',
            '__IMG__' => '/static/img',
        ],
        // 是否开启模板编译缓存,设为false则每次都会重新编译
        'tpl_cache' => !env('app_debug', false),
    ]
];
