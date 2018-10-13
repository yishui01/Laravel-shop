<?php

namespace App\Observer;

use App\Jobs\SyncOneProductToES;
use App\Models\Product;
use App\Models\ProductSku;

class ProductObserver
{
    public function saved(Product $product)
    {
        //更新ES索引
        dispatch(new SyncOneProductToES($product));
        //设置秒杀商品到redis中
        if($product->type == Product::TYPE_SECKILL) {
            $product->setSeckillToRedis();
        }
    }

}