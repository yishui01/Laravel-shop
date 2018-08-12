<?php

namespace App\Policies;

use App\Models\SocialInfo;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function own(User $user, Order $order)
    {
        return $order->user_id == $user->id;
    }

    //小程序端查看订单是否是本人的
    public function miniOwn(SocialInfo $user, Order $order)
    {
        $orderService = new OrderService();
        //获取这个用户下的所有订单ID，看请求的订单ID是否在ID数组中
        $all_orders_id = $orderService->getAllOrders($user, true)->toArray();
        return in_array($order->id, $all_orders_id);
    }
}
