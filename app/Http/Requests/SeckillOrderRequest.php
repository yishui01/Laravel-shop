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
                    // 从 Redis 中读取数据
                    $stock = \Redis::get('seckill_sku_'.$value);
                    // 如果是 null 代表这个 SKU 不是秒杀商品
                    if (is_null($stock)) {
                        return $fail('该商品不存在');
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
                    /*if ($order = Order::query()
                        // 筛选出当前用户的订单
                        ->where('user_id', $this->user()->id)
                        ->whereHas('items', function ($query) use ($value) {
                            // 筛选出包含当前 SKU 的订单
                            $query->where('product_sku_id', $value);
                        })
                        ->where(function ($query) {
                            // 已支付的订单
                            $query->whereNotNull('paid_at')
                                // 或者未关闭的订单
                                ->orWhere('closed', false);
                        })
                        ->first()) {
                        if ($order->paid_at) {
                            return $fail('你已经抢购了该商品');
                        }

                        return $fail('你已经下单了该商品，请到订单页面支付');
                    }*/
                },
            ],
        ];
    }
}