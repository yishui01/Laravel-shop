<?php

namespace App\Observer;

use App\Jobs\SyncOneProductToES;
use App\Models\Attribute;

class AttributeObserver
{

    public function saved(Attribute $attribute)
    {
        dispatch(new SyncOneProductToES($attribute->product)); //更新es索引数据
    }

    public function deleted($attribute)
    {
        if($attribute->product){
            dispatch(new SyncOneProductToES($attribute->product));
        }
    }
}