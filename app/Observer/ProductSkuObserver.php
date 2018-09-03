<?php

namespace App\Observer;

use App\Models\Product;
use App\Models\ProductSku;

class ProductSkuObserver
{

    public function saved(ProductSku $sku)
    {
        $this->updateMinPrice($sku->product_id);
    }

    public function deleted($sku)
    {
        $this->updateMinPrice($sku->product_id);
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