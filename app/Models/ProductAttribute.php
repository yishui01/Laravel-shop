<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    protected $fillable = ['name','hasmany','val'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}
