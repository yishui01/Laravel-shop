<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Request;
use App\Events\OrderPaid;
use App\Exceptions\InvalidRequestException;
class PaymentController extends Controller
{

    //小程序微信支付接口,返回小程序拉起微信支付所需要的参数
    public function miniPayByWechat(Order $order, Request $request)
    {
        //验证这个订单是不是这个用户的订单
        $this->authorize('miniOwn', $order);
        // 订单已支付或者已关闭
        if ($order->paid_at || $order->closed) {
            throw new InvalidRequestException('订单状态不正确');
        }
        $social_info = $this->user();
        $payment_service = new PaymentService();
        $mini_need_param = $payment_service->miniPayByWechat($order, $social_info);
        return $this->response->array($mini_need_param)->setStatusCode(201);
    }

}
