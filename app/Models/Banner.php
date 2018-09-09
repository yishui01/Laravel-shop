<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    public $fillable = ['url','type'];

    public function scopeShow($query)
    {
        return $query->where('isshow', 1);
    }

    public function getUrlAttribute($value)
    {
        if (strpos($value, 'http') === false) {
            return env('APP_URL').'/uploads/'.$value;
        }
        return $value;
    }
}
