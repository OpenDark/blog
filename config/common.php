<?php
return [
    'domain' => env('APP_URL', '//127.0.0.1:8787'),
    'authcode_key' => env('AUTHCODE_KEY', 'blog'),
    'hashids_salt' => env('HASHIDS_SALT', 'blog'),
];
