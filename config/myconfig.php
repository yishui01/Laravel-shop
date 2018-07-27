<?php

return [
    'prefix' => [ //缓存前缀配置
        'verify_email'=>'email_verification_', //验证邮箱的缓存前缀
    ],
    'order' => [
        'order_ttl'=>env('order_ttl',1800)
    ]
];