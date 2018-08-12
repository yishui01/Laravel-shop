<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\InvalidRequestException;
use App\Models\Order;
use App\Models\SocialInfo;
use Illuminate\Http\Request;
use App\Http\Requests\Api\V1\OrderRequest;
use App\Services\OrderService;
use App\Models\UserAddress;
use App\Models\CouponCode;
use App\Exceptions\CouponCodeUnavailableException;
use Illuminate\Support\Facades\Auth;
use App\Transformers\OrderTransformer;
class OrdersController extends Controller
{
    //下单
    public function store(OrderRequest $request, OrderService $orderService)
    {

        $user = Auth::guard('api')->user();

        $address = UserAddress::find($request->input('address_id'));

        $coupon = null;

        // 如果用户提交了优惠码
        if ($code = $request->input('coupon_code')) {
            $coupon = CouponCode::where('code', $code)->first();
            if (!$coupon) {
                $this->response->error('优惠券不存在', 422);
            }
        }

        return $orderService->store($user, $address, $request->input('remark'), $request->input('items'), $coupon, 'mini');
    }

    //订单列表页
    public function index(OrderService $orderService)
    {
        //把PC端的订单和第三方的订单一起返回给用户
        $social_user = $this->user();
        $orders = $orderService->getAllOrders($social_user);

        //处理商品图片和订单状态
        foreach ($orders as &$order)
        {
            foreach ($order['items'] as &$item) {
                $item->product->fullImage = $item->product->fullImage;
            }
            /**
             * 订单状态：
             * 1 => '待付款'
             * 2 => '待发货'
             * 3 => '已发货（待收货）'
             * 4 => '待评价（已收货）',
             * 5 => 已完成
             */
            if ($order->paid_at) {
                //如果已经支付
                if ($order->refund_status == Order::REFUND_STATUS_SUCCESS || $order->refund_status == Order::REFUND_STATUS_FAILED) {
                    $order['status'] = 5; //已经退款完毕，已完成
                } else {
                    //否则显示五六状态
                    if (Order::SHIP_STATUS_PENDING == $order->ship_status) {
                        $order['status'] = 2;
                    } else if(Order::SHIP_STATUS_DELIVERED == $order->ship_status) {
                        $order['status'] = 3;
                    } else if(Order::SHIP_STATUS_RECEIVED == $order->ship_status) {
                        //已收货
                        if ($order->reviewed) {
                            $order['status'] = 5; //已评价
                        } else {
                            $order['status'] = 4;
                        }
                    }
                }
            } else if($order->closed) {
                //订单已关闭
                $order['status'] = 5;
            } else {
                //未支付
                $order['status'] = 1;
            }
        }
        return $this->response->array($orders)->setStatusCode(201);
        //return $this->response->collection($orders, new OrderTransformer($orders))->setStatusCode(201);

    }
}
