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
            $sku->product->setSeckillToRedis();
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