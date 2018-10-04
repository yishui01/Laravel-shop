<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    protected $fillable = ['name','hasmany','val', 'product_id', 'is_search'];

    //与商品表的关联
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    //与属性值表的关联
    public function attribute()
    {
        return $this->hasMany(Attribute::class,'attr_id');
    }


}
