<?php

namespace App\Models;

use App\Exceptions\CouponCodeUnavailableException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Exceptions\InvalidRequestException;
class Product extends Model
{
    const TYPE_NORMAL = 'normal';
    const TYPE_CROWDFUNDING = 'crowdfunding';
    const TYPE_SECKILL = 'seckill';

    public static $typeMap = [
        self::TYPE_NORMAL  => '普通商品',
        self::TYPE_CROWDFUNDING => '众筹商品',
        self::TYPE_SECKILL => '秒杀商品',
    ];

    protected $fillable = [
        'title', 'description', 'image', 'on_sale',
        'rating', 'sold_count', 'review_count', 'price','category_id','type','long_title'
    ];

    protected $casts = [
        'on_sale' => 'boolean', // on_sale 是一个布尔类型的字段
    ];

    //与众筹表的关联
    public function crowdfunding()
    {
        return $this->hasOne(CrowdfundingProduct::class);
    }

    //与秒杀商品的关联
    public function seckill()
    {
        return $this->hasOne(SeckillProduct::class);
    }

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
        return file_url($this->attributes['image']);
    }

    //获取这个商品下的属性以及属性值（可选属性可唯一属性都找出来）
    public function getPropertiesAttribute()
    {
        //所有的属性（包括可选和唯一）
        ProductAttribute::where('product_id', $this->attributes['id'])->get();
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
        if (!count($skus))  throw new CouponCodeUnavailableException('该商品没有库存啦');
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

    //将商品信息转换成ElasticSearch的存储数组
    public function toESArray()
    {
        // 只取出需要的字段
        $arr = array_only($this->toArray(), [
            'id',
            'type',
            'title',
            'category_id',
            'long_title',
            'on_sale',
            'rating',
            'sold_count',
            'review_count',
            'price',
        ]);

        // 如果商品有类目，则 category 字段为类目名数组，否则为空字符串
        $arr['category'] = $this->category ? explode('-', $this->category->full_name) : '';
        // 类目的 path 字段
        $arr['category_path'] = $this->category ? $this->category->path : '';
        // strip_tags 函数可以将 html 标签去除
        $arr['description'] = strip_tags($this->description);
        // 只取出需要的 SKU 字段
        $arr['skus'] = $this->skus->map(function (ProductSku $sku) {
            return array_only($sku->toArray(), ['title', 'description', 'price']);
        });
        $all_properties = $this->getProperties();
        $arr['properties'] = array_except($all_properties, 'is_search');
        // 只取出参与搜索的商品属性字段
        $arr['search_properties'] = [];
        foreach ($all_properties as $k=>$v){
            if($v['is_search'] == 1) {
                $arr['search_properties'][] = $v;
            }
        }

        return $arr;
    }

    //获取这个商品下的所有属性名=>属性值（包括可选和唯一两种属性）
    public function getProperties()
    {
        //商品属性有两种，
        //唯一，可以直接从属性表中的val字段拿到属性值
        //可选，属性表只记录了属性名，属性值存在Attrubutes表中
        $property_arr = [];
        foreach ($this->pro_attr as $k => $attr) {
            if ($attr->hasmany == 0) {
                //唯一属性
                $property_arr[] = ['is_search' => $attr->is_search, 'name' => $attr->name, 'value' => $attr->val, 'search_value' => $attr->name .':'. $attr->val];
            } else {
                //可选属性
                $select_arr = Attribute::where('attr_id', $attr->id)->get(); //所有的可选属性值
                foreach ($select_arr as $select) {
                    $property_arr[] = ['is_search' => $attr->is_search, 'name' => $attr->name, 'value' => $select->attr_val, 'search_value' => $attr->name .':'. $select->attr_val];
                }
            }
        }
        return $property_arr;
    }

    //设置秒杀商品到redis中
    public function setSeckillToRedis()
    {
        if ($this->type == self::TYPE_SECKILL) {
            //设置秒杀商品
            $this->load(['seckill']);
            // 获取当前时间与秒杀结束时间的差值
            $diff = $this->seckill->end_at->getTimestamp() - time();
            // 遍历商品 SKU
            $this->skus->each(function (ProductSku $sku) use ($diff) {
                $stock_key = 'seckill_sku_'.$sku->id;
                $sku_time_key = 'seckill_sku_'.$sku->id.'_time';
                // 如果秒杀商品是上架并且尚未到结束时间
                if ($this->on_sale && $diff > 0) {
                    // 将剩余库存写入到 Redis 中，并设置该值过期时间为秒杀截止时间
                    \Redis::setex($stock_key, $diff, $sku->stock);
                    \Redis::setex($sku_time_key, $diff, $this->seckill->start_at->timestamp.'#'.$this->seckill->end_at->timestamp);
                } else {
                    // 否则将该 SKU 的库存值从 Redis 中删除
                    \Redis::del($stock_key);
                    \Redis::del($sku_time_key);
                }
            });
        }

    }

    public function scopeByIds($query, $ids)
    {
        return $query->whereIn('id', $ids)->orderByRaw(sprintf("FIND_IN_SET(id, '%s')", join(',', $ids)));
    }

}
