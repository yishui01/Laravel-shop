<?php

namespace App\Transformers;

use App\Models\Product;
use League\Fractal\TransformerAbstract;

class ProductTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['category'];
    public function transform(Product $product)
    {
        return [
            'id'=>$product->id,
            'title'=>$product->title,
            'description'=>$product->description,
            'category_id'=>$product->category_id,
            'image'=>strpos($product->image, 'http') === false ? env('APP_URL').'/uploads/'.$product->image : $product->image,
            'rating'=>$product->rating,
            'sold_count'=>$product->sold_count,
            'review_count'=>$product->review_count,
            'price'=>$product->price,
        ];
    }

    public function includeCategory(Product $product)
    {
        if ($product->category) {
            return $this->item($product->category, new CategoryTransformer());
        }

    }

}