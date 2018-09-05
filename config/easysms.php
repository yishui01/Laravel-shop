<?php
return [
    'sms_config' => [
        'expire' => '15', //验证码有效时间（分钟）
        'template_id' => '157503', //模板ID157503
    ],
    'send_config' => [
        // HTTP 请求的超时时间（秒）
        'timeout' => 5.0,

        // 默认发送配置
        'default' => [
            // 网关调用策略，默认：顺序调用
            'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,

            // 默认可用的发送网关
            'gateways' => [
                'qcloud',
            ],
        ],
        // 可用的网关配置
        'gateways' => [
            'errorlog' => [
                'file' => '/tmp/easy-sms.log',
            ],
            //腾讯云短信发送
            'qcloud' => [
                'sdk_app_id' => env('TX_SMS_APPID'),
                'app_key' => env('TX_SMS_APPKEY'),
                'sign_name' => '', // 短信签名，如果使用默认签名，该字段可缺省（对应官方文档中的sign）
            ],
        ],
    ]

];