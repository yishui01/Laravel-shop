<?php

namespace App\Policies;

use App\Models\User;
use App\Services\OrderService;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderPolicy
{
    use HandlesAuthorization;

    //验证这个订单是否是当前用户的
    public function own(User $user, Order $order)
    {
        return $order->user_id == $user->id;
    }

}
