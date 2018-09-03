<?php

namespace App\Observer;

use App\Models\Product;
use App\Models\ProductSku;

class ProductSkuObserver
{

    public function saving(ProductSku $sku)
    {
        //每次保存的时候，找出当前商品中价格最低的sku，更新到product表
        $product = Product::find($sku->product_id);
        $sku = $product->skus;
        $min = collect($sku)->min('price');
        $product->price = $min ?? 0;
        $product->save();
    }
}