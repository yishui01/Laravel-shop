<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\UserAddress;

class UserAddressPolicy
{
    use HandlesAuthorization;

    //验证这个收货地址是否是当前用户的
    public function update(User $currentUser, UserAddress $userAddress)
    {
        return $currentUser->id == $userAddress->user_id;
    }

}
