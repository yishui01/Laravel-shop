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

    //验证这个订单是否是当前用户的
    public function own($user, Order $order)
    {
        $builder = create_relation_builder($user, \App\Models\Order::class);
        //找出用户所有的订单ID，看这个ID是否包含在里面
        return in_array($order->id, $builder->pluck('id')->toArray());
    }

}
