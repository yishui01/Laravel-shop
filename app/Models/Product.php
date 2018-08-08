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
        $skus = $this->skus()->get();
        //现有的唯一属性
        $unique_attr = $this->pro_attr()->where('hasMany', '0')->get();
        $select_attr = [];
        //现有的可选属性要根据已存在的skus反推出来，不能直接查询，否则会有BUG,商品属性值表没有删除的操作
        //一旦录入错误的sku，删除时不会连带删除属性值表，也就是说错误的属性值还保留在表内，所以不能通过已有
        //属性名字来直接查询出所有属性名下的所有属性值
        if (count($skus)) {
            $val_id = []; //所有sku具有的属性值ID集合
            foreach ($skus as $k=>$v) {
                if(!empty($v['attributes'])) {
                    $val_id = array_unique(array_merge($val_id, explode(',', $v['attributes'])));
                }
            }
            //如果有属性值的话，找出属性值对应的属性名，最后组合好赋值给$select_attr
            if (!empty($val_id)) {
                $pro_attr = Attribute::with('attr')->whereIn('id', $val_id)->get();
                //根据属性值构造出最终的可选属性数组
                foreach ($pro_attr as $v) {
                    if (!empty($v['attr'])) {
                        if (isset($select_attr[$v['attr']['id']])) {
                            $select_attr[$v['attr']['id']]['data'][] = $v->toArray();
                        } else {
                            $select_attr[$v['attr']['id']] = [
                                'id'=>$v['attr']['id'],
                                'name'=>$v['attr']['name'],
                                'data'=>[$v->toArray()]
                            ];
                        }
                    }
                }
            }

        } else {
            throw new InvalidRequestException('该商品没有库存啦');
        }

        $select_attr = array_values($select_attr); //重置索引

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
