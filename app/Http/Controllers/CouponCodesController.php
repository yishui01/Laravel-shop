<?php

namespace App\Http\Controllers;

use App\Models\CouponCode;
use Illuminate\Http\Request;
use App\Exceptions\CouponCodeUnavailableException;
class CouponCodesController extends Controller
{
    public function show($code, Request $request)
    {
        //判断优惠券是否存在以及是否生效
        if (!$record = CouponCode::where('code', $code)->first()) {
            throw new CouponCodeUnavailableException('优惠券不存在');
        }
        $record->checkAvailable($request->user());

        return $record;

    }
}
