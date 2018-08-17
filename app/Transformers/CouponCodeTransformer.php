<?php

namespace App\Transformers;

use App\Models\CouponCode;
use League\Fractal\TransformerAbstract;

class CouponCodeTransformer extends TransformerAbstract
{
    public function transform(CouponCode $couponCode)
    {
        return [
            'name' => $couponCode->name,
            'description' => $couponCode->description,
            'type' => $couponCode->type,
            'value' => $couponCode->value,
            'code' => $couponCode->code,
            'min_amount' => $couponCode->min_amount,
            'not_before' => $couponCode->not_before ? $couponCode->not_before->toDateString() : null,
            'not_after' => $couponCode->not_after ? $couponCode->not_after->toDateString() : null
        ];
    }
}