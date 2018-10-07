<?php

namespace App\Observer;

use App\Jobs\SyncOneProductToES;
use App\Models\Product;
use App\Models\ProductSku;

class ProductSkuObserver
{

    public function saved(ProductSku $sku)
    {
        $this->updateMinPrice($sku->product_id); //更新商品最低价格
        dispatch(new SyncOneProductToES($sku->product)); //更新es索引数据
        if($sku->product->type == Product::TYPE_SECKILL) {
            //设置秒杀商品
            $product = $sku->product;
            // 商品重新加载秒杀字段
            $product->load(['seckill']);
            // 获取当前时间与秒杀结束时间的差值
            $diff = $product->seckill->end_at->getTimestamp() - time();
            // 遍历商品 SKU
            $product->skus->each(function (ProductSku $sku) use ($diff, $product) {
                // 如果秒杀商品是上架并且尚未到结束时间
                if ($product->on_sale && $diff > 0) {
                    // 将剩余库存写入到 Redis 中，并设置该值过期时间为秒杀截止时间
                    \Redis::setex('seckill_sku_'.$sku->id, $diff, $sku->stock);
                } else {
                    // 否则将该 SKU 的库存值从 Redis 中删除
                    \Redis::del('seckill_sku_'.$sku->id);
                }
            });
        }


    }

    public function deleted($sku)
    {
        $this->updateMinPrice($sku->product_id);
        if($sku->product){
            dispatch(new SyncOneProductToES($sku->product));
        }
    }

    protected function updateMinPrice($product_id)
    {
        //每次保存的时候，找出当前商品中价格最低的sku，更新到product表
        $product = Product::find($product_id);
        $sku = $product->skus;
        $min = collect($sku)->min('price');
        $product->price = $min ?? 0;
        $product->save();
    }
}