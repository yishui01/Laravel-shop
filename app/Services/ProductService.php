<?php
namespace App\Services;

use App\Models\Product;
use App\SearchBuilders\ProductSearchBuilder;

class ProductService
{
    public function getSimilarProductIds(Product $product, $amount)
    {
        // 遍历当前商品的属性
        $properties = $product->getProperties();
        if (count($properties) === 0) {
            return [];
        }
        $builder = (new ProductSearchBuilder())->onSale()->paginate($amount, 1);
        foreach ($properties as $property) {
            // 添加到 should 条件中
            $builder->propertyFilter($property['name'] .':'.$property['value'], 'should');
        }
        // 设置最少匹配一半属性
        $builder->minShouldMatch(ceil(count($properties) / 2));
        $params = $builder->getParams();
        // 同时将当前商品的 ID 排除
        $params['body']['query']['bool']['must_not'] = [['term' => ['_id' => $product->id]]];
        // 搜索
        $result = app('es')->search($params);
        $similarProductIds = collect($result['hits']['hits'])->pluck('_id')->all();
        // 根据 Elasticsearch 搜索出来的商品 ID 从数据库中读取商品数据
        $similarProducts   = Product::query()
            ->whereIn('id', $similarProductIds)
            ->orderByRaw(sprintf("FIND_IN_SET(id, '%s')", join(',', $similarProductIds)))
            ->get();

        return collect($result['hits']['hits'])->pluck('_id')->all();
    }
}