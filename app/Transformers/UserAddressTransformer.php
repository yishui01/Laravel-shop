<?php

namespace App\Transformers;

use App\Models\UserAddress;
use League\Fractal\TransformerAbstract;

class UserAddressTransformer extends TransformerAbstract
{
    public function transform(UserAddress $userAddress)
    {
        return [
            'id'         => $userAddress->id,
            'province'   => $userAddress->province,  //省
            'city'       => $userAddress->city,      //市
            'district'   => $userAddress->district,  //区
            'address'    => $userAddress->address,   //详细地址
            'zip'        => $userAddress->zip,
            'contact_name'=>$userAddress->contact_name,
            'contact_phone'=>$userAddress->contact_phone,
        ];
    }
}