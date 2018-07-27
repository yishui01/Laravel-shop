<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['amount','product_sku_id'];
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function productSku()
    {
        return $this->belongsTo(ProductSku::class);
    }
}
