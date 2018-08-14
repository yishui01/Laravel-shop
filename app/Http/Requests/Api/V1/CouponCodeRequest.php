<?php

namespace App\Http\Requests\Api\V1;


class CouponCodeRequest extends ApiBaseRequest
{

    public function rules()
    {
        return [
            'code' => ['required']
        ];
    }

    public function attributes()
    {
        return [
            'code' => '优惠券码'
        ];
    }
}
