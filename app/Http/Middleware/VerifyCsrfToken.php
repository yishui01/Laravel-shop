<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'payment/alipay/notify', //普通支付支付宝（服务端）
        'payment/wechat/notify', //普通支付微信
        'payment/wechat/refund_notify', //普通退款微信
        'installments/alipay/notify',       //分期支付宝（服务端）
        'installments/wechat/notify',        //分期微信
        'installments/wechat/refund_notify', //分期退款微信回调
    ];
}
