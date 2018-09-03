<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Exceptions\InvalidRequestException;
class Product extends Model
{
    protected $fillable = [
        'title', 'description', 'image', 'on_sale',
        'rating', 'sold_count', 'review_count', 'price','category_id'
    ];

    protected $casts = [
        'on_sale' => 'boolean', // on_sale 是一个布尔类型的字段
    ];

    // 与商品SKU关联
    public function skus()
    {
        return $this->hasMany(ProductSku::class);
    }

    //与商品属性表关联
    public function pro_attr()
    {
        return $this->hasMany(ProductAttribute::class);
    }

    //与商品值表关联
    public function attr()
    {
        return $this->hasMany(Attribute::class);
    }

    //与分类表的关联
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    //补全商品图片url
    public function getFullImageAttribute()
    {
        // 如果 image 字段本身就已经是完整的 url 就直接返回
        if (Str::startsWith($this->attributes['image'], ['http://', 'https://'])) {
            return $this->attributes['image'];
        }
        return \Storage::disk('public')->url($this->attributes['image']);
    }

    //获取商品详情页面的SKU以及可选属性和唯一属性
    public function getSkuDetail()
    {
        //现有的sku
        $skus = $this->skus;
        //唯一属性
        $unique_attr = $this->pro_attr()->where('hasMany', '0')->get();
        //可选属性
        $select_attr = $this->pro_attr()->with('attribute')
            ->where('hasMany', '1')->get()->toArray();
        if (!count($skus))  throw new InvalidRequestException('该商品没有库存啦');
        return [
            'select_attr'=>$select_attr,
            'skus' => $skus,
            'unique_attr' =>$unique_attr
        ];
    }

    //获取商品评价，用于详情页面的展示
    public function getReview()
    {
        return OrderItem::query()
            ->with(['order.user'])
            ->where('product_id', $this->id)
            ->whereNotNull('reviewed_at')
            ->orderBy('reviewed_at', 'desc')
            ->limit(10)->get();
    }



}
