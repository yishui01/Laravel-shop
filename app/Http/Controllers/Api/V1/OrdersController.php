<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\InvalidRequestException;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Requests\Api\V1\OrderRequest;
use App\Services\OrderService;
use App\Models\UserAddress;
use App\Models\CouponCode;
use App\Exceptions\CouponCodeUnavailableException;
use Illuminate\Support\Facades\Auth;
use App\Transformers\OrderTransformer;
use Illuminate\Support\Facades\Log;

class OrdersController extends Controller
{
    //下单
    public function store(OrderRequest $request, OrderService $orderService)
    {

        $user = $this->user();

        $address = UserAddress::find($request->input('address_id'));

        $coupon = null;

        // 如果用户提交了优惠码
        if ($code = $request->input('coupon')) {
            $coupon = CouponCode::where([
                ['code','=', $code],
                ['enabled','=', 1],
            ])->firstOrFail();
        }
        try {
            //dingoAPI不能触发laravel自定义异常的render方法吗，还要自己手动捕获才行，web端就可以触发异常自带的render方法啊
            $order = $orderService->store($user, $address, $request->input('remark'),
                $request->input('items'), $coupon, 'mini');
        } catch (\Exception $e){
            if ($e->getCode() == 500) {
                return $this->recordAndResponse($e, '下单错误：', '网络错误，下单失败');
            } else {
                return $this->response->error($e->getMessage(), $e->getCode());
            }
        }

        return $this->response->item($order, new OrderTransformer())
            ->setStatusCode($this->success_code);
    }

    //订单列表页
    public function index(OrderService $orderService)
    {

        $user = $this->user();
        $orders = $orderService->getAllOrders($user);

        //处理商品图片和订单状态
        foreach ($orders as &$order)
        {
            foreach ($order['items'] as &$item) {
                //拼接图片地址
                $item->product->fullImage = $item->product->fullImage;
            }
            //放入订单状态字段，方便前端筛选
            $order['status'] = $this->setStatus($order);
        }
        return $this->response->collection($orders, new OrderTransformer())
            ->setStatusCode($this->success_code);

    }

    //订单详情
    public function show(Order $order)
    {
        $order_info = $order->with(['items.productSku', 'items.product'])->findOrFail($order->id);
        foreach ($order_info['items'] as &$item) {
            //拼接图片地址
            $item->product->fullImage = $item->product->fullImage;
        }
        $order_info['status'] = $this->setStatus($order_info);
        return $this->response->item($order_info, new OrderTransformer())
            ->setStatusCode($this->success_code);
    }

    //判断当前订单状态，返回给前端方便筛选
    public function setStatus(Order $order)
    {
        /**
         * 订单状态：
         * 1 => '待付款'
         * 2 => '待发货'
         * 3 => '已发货（待收货）'
         * 4 => '待评价（已收货）',
         * 5 => 已完成
         */
        $status = null;
        if ($order->paid_at) {
            //如果已经支付
            if ($order->refund_status == Order::REFUND_STATUS_SUCCESS || $order->refund_status == Order::REFUND_STATUS_FAILED) {
                $status = 5; //已经退款完毕，已完成
            } else {
                //否则显示五六状态
                if (Order::SHIP_STATUS_PENDING == $order->ship_status) {
                    $status = 2;
                } else if(Order::SHIP_STATUS_DELIVERED == $order->ship_status) {
                    $status = 3;
                } else if(Order::SHIP_STATUS_RECEIVED == $order->ship_status) {
                    //已收货
                    if ($order->reviewed) {
                        $status = 5; //已评价
                    } else {
                        $status = 4;
                    }
                }
            }
        } else if($order->closed) {
            //订单已关闭
            $status = 5;
        } else {
            //未支付
            $status = 1;
        }

        return $status;
    }

    //删除订单
    public function destroy(Order $order)
    {
        $this->authorize('own', $order);
        if ($order->paid_at || $order->closed) {
            return $this->response->error('订单状态不正确', 403);
        }
        $order->closed = 1;
        $order->save();
        return $this->response->noContent()->setStatusCode(201);
    }
}
