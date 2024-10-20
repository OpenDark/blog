<?php
return [
    'domain' => env('APP_URL', '//127.0.0.1:8787'),
    'imgcdn' => env('IMG_CDN', '//127.0.0.1:8787'),
    'authcode_key' => env('AUTHCODE_KEY', 'blog'),
    'hashids_salt' => env('HASHIDS_SALT', 'blog'),
    'mail_limit' => [
        'max' => 5, // 最大发送次数
        'valid' => 10, // 验证码有效期
        'interval' => 3, // 发送间隔
    ],
    'mail_config' => [ // 邮件发送配置，第三方开启POP3/SMTP服务，设置授权码
        'port' => 465,
        'smtp' => 'smtp.sina.com',
        'name' => '若羽博客',
        'user' => 'rwyphp@sina.com',
        'pwd' => 'da1b5f65fcb7c315',
    ],
];
