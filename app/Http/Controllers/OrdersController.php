<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Http\Requests\Request;
use App\Models\ProductSku;
use App\Models\UserAddress;
use App\Models\Order;
use App\Services\CartService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\SystemException;
use App\Jobs\CloseOrder;
use App\Services\OrderService;
use App\Exceptions\InvalidRequestException;
class OrdersController extends Controller
{
    //下单
    public function store(OrderRequest $request, OrderService $orderService)
    {

        $user    = $request->user();
        $address = UserAddress::find($request->input('address_id'));
        return $orderService->store($user, $address, $request->input('remark'), $request->input('items'));
    }

    //订单列表页
    public function index()
    {
        $orders = Auth::user()->orders()->with(['items.productSku', 'items.product']) ->orderBy('created_at', 'desc')->paginate();
        return view('orders.index', ['orders' => $orders]);
    }

    //订单详情页面
    public function show(Order $order)
    {
        $this->authorize('own', $order);
        return view('orders.show', ['order'=>$order->load(['items.productSku', 'items.product'])]);
    }

    //用户确认收货
    public function received(Order $order, Request $request)
    {

        // 校验权限
        $this->authorize('own', $order);
        // 判断订单的发货状态是否为已发货
        if ($order->ship_status !== Order::SHIP_STATUS_DELIVERED) {
            throw new InvalidRequestException('该订单还未发货');
        }
        $order->ship_status = Order::SHIP_STATUS_RECEIVED;
        $order->save();
        // 返回订单信息
        return $order;
    }


}