<?php

namespace App\Policies;

use App\Models\SocialInfo;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\UserAddress;

class UserAddressPolicy
{
    use HandlesAuthorization;

    //验证这个收货地址是否是当前用户的
    public function update($currentUser, UserAddress $userAddress)
    {
        $builder = create_relation_builder($currentUser, \App\Models\UserAddress::class);
        $id_obj = $builder->pluck('id'); //用户可以修改的所有AddressID
        return in_array($userAddress->id, $id_obj->toArray());
    }

}
