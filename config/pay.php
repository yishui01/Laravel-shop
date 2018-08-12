<?php
return [
    'alipay' => [
        'app_id'         => env('ALI_APPID'),
        'ali_public_key' => env('ALI_PUBLIC_KEY'),
        'private_key'    => env('ALI_PRIVATE_KEY'),
        'log'            => [
            'file' => storage_path('logs/alipay.log'),
        ],
        //服务端回调
        'notify_url_route_name' =>'payment.alipay.notify', //这里不能调用route()，只能先写路由名字
        //前端回调
        'return_url_route_name' =>'payment.alipay.return',
    ],
    'wechat' => [
        'app_id'      => env('WECHAT_APPID'),
        'mch_id'      => env('WECHAT_MCH_ID'),
        'key'         => env('WECHAT_KEY'),
        'cert_client' => resource_path('wechat_pay/apiclient_cert.pem'),
        'cert_key'    => resource_path('wechat_pay/apiclient_key.pem'),
        'log'         => [
            'file' => storage_path('logs/wechat_pay.log'),
        ],
        //服务端回调
        'notify_url_route_name' =>'payment.wechat.notify',
    ],
    'mini' => [
        'app_id'      => env('WECHAT_MINI_PROGRAM_APPID'),
        'mch_id'      => env('WECHAT_MCH_ID'),
        'key'         => env('WECHAT_KEY'),
        'cert_client' => resource_path('wechat_pay/apiclient_cert.pem'),
        'cert_key'    => resource_path('wechat_pay/apiclient_key.pem'),
        'log'         => [
            'file' => storage_path('logs/wechat_pay.log'),
        ],
        //服务端回调
        'notify_url_route_name' =>'payment.wechat.notify',
    ],
];