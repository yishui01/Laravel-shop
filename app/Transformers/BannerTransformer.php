<?php

namespace App\Transformers;

use App\Models\Banner;
use League\Fractal\TransformerAbstract;

class BannerTransformer extends TransformerAbstract
{
    public function transform(Banner $banner)
    {
        return [
            'url'=>$banner->url,
            'sort'=>$banner->sort,
            'title'=>$banner->title,
            'link'=>$banner->link,
            'type'=>$banner->type,
            'place'=>$banner->place,
            'product_id'=>$banner->product_id,
        ];
    }
}