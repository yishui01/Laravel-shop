<?php

namespace App\Http\Requests;

use App\Exceptions\InvalidRequestException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\Rule;
use App\Models\ProductSku;
use App\Models\Product;
use App\Models\Order;

class SeckillOrderRequest extends Request
{
    public function rules()
    {
        return [
            // 将原本的 address_id 删除
            'address.province'      => 'required',
            'address.city'          => 'required',
            'address.district'      => 'required',
            'address.address'       => 'required',
            'address.zip'           => 'required',
            'address.contact_name'  => 'required',
            'address.contact_phone' => 'required',
            'sku_id'     => [
                'required',
                function ($attribute, $value, $fail) {
                    // 是否为秒杀商品
                    $stock = \Redis::get('seckill_sku_'.$value);
                    if (is_null($stock)) {
                        return $fail('该商品不存在');
                    }
                    //是否到了秒杀时间
                    $sku_time_key = 'seckill_sku_'.$value.'_time';
                    $time = \Redis::get($sku_time_key);
                    $time_arr = explode('#', $time);
                    if(time() < $time_arr[0] || time() > $time_arr[1]) {
                        return $fail('当前不在秒杀时间');
                    }
                    // 判断库存
                    if ($stock < 1) {
                        return $fail('该商品已售完');
                    }
                    //延迟校验是否登录，仅当秒杀商品还有剩余时再校验
                    if (!$user = \Auth::user()) {
                        throw new AuthenticationException('请先登录');
                    }
                    // 从 Redis 中读取已经下单了并且订单未过期的用户数据，这些用户不可重复下单
                    $have_user = \Redis::get('seckill_sku_'.$value.'_user_'.$this->user()->id);
                    if ($have_user) {
                        return $fail('你已经下单了该商品，请到订单页面支付');
                    }

                },
            ],
        ];
    }
}