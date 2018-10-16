<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Models\Attribute;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\SearchBuilders\ProductSearchBuilder;
class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $page    = $request->input('page', 0) > 1 ? $request->input('page', 0) : 1;
        $perPage = 8;

        // 新建查询构造器对象，设置只搜索上架商品，设置分页
        $builder = (new ProductSearchBuilder())->onSale()->paginate($perPage, $page);

        //类目筛选
        if ($request->input('category_id') && $category = Category::find($request->input('category_id'))) {
            $builder->category($category);
        }

        //关键字查找
        if ($search = $request->input('search', '')) {
            $keywords = array_filter(explode(' ', $search));
            $builder->keywords($keywords);
        }

        //聚合  只有当用户有输入搜索词或者使用了类目筛选的时候才会做
        if ($search || isset($category)) {
            // 调用查询构造器的分面搜索
            $builder->aggregateProperties();
        }

        //商品属性筛选
        $propertyFilters = [];
        if ($filterString = $request->input('filters')) {
            // 将获取到的字符串用符号 | 拆分成数组
            $filterArray = explode('|', $filterString);
            foreach ($filterArray as $filter) {
                // 将字符串用符号 : 拆分成两部分并且分别赋值给 $name 和 $value 两个变量
                list($name, $value) = explode(':', $filter);
                // 将用户筛选的属性添加到数组中
                $propertyFilters[$name] = $value;
                // 调用查询构造器的属性筛选
                $builder->propertyFilter($filter);
            }
        }

        //排序
        if ($order = $request->input('order', '')) {
            if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
                    // 调用查询构造器的排序
                    $builder->orderBy($m[1], $m[2]);
                }
            }
        } else {
            //默认按照id进行排序
            $builder->orderBy('id', 'asc');
        }

        $result = app('es')->search($builder->getParams());
        //dd($result);
        $properties = [];
        // 如果返回结果里有 aggregations 字段，说明做了分面搜索

        if (isset($result['aggregations'])) {
            // 使用 collect 函数将返回值转为集合
            $properties = collect($result['aggregations']['properties']['properties']['buckets'])
                ->map(function ($bucket) {
                    // 通过 map 方法取出我们需要的字段
                    return [
                        'key'    => $bucket['key'],
                        'values' => collect($bucket['value']['buckets'])->pluck('key')->all(),
                    ];
                })->filter(function ($property) use ($propertyFilters) {
                    // 过滤掉只剩下一个值 或者 已经在筛选条件里的属性
                    return count($property['values']) > 1 && !isset($propertyFilters[$property['key']]) ;
                });

        }

        // 通过 collect 函数将返回结果转为集合，并通过集合的 pluck 方法取到返回的商品 ID 数组
        $productIds = collect($result['hits']['hits'])->pluck('_id')->all();
        // 通过 whereIn 方法从数据库中读取商品数据
        $products = Product::query()->byIds($productIds)->get();
        // 返回一个 LengthAwarePaginator 对象
        $pager = new LengthAwarePaginator($products, $result['hits']['total'], $perPage, $page, [
            'path' => route('products.index', false), // 手动构建分页的 url
        ]);

        return view('products.index', [
            'products' => $pager,
            'category' => $category ?? null,
            'filters'  => [
                'search' => $search,
                'order'  => $order,
            ],
            'properties' => $properties, //列出来的商品属性
            'propertyFilters' => $propertyFilters, //已经选择的商品属性（面包屑导航）
            // 这个已经用viewcomposer自动注入了
            //// 'categoryTree' => isset($category) ? $category->getTree() : (new Category())->getTree(),
        ]);
    }

    //商品详情
    public function show(Product $product, ProductService $productService)
    {
        if (!$product || !$product->on_sale) {
            throw new InvalidRequestException('该商品未上架');
        }
        session(['product_detail_id' => $product->id]); //记录用户最新浏览的是哪个商品，用于未登录-》登陆跳转
        /**************************搜索前4个相似的上架商品**************************************/
        $similarProductIds = $productService->getSimilarProductIds($product, 4);
        $similarProducts   = $similarProducts   = Product::query()->byIds($similarProductIds)->get();
        /************************搜索相似商品结束*******************************************/

        $sku_data = $product->getSkuDetail(); //sku以及属性数据

        $favorite = false;                    //是否收藏了该商品
        if ($user = Auth::user()) {
            //从中间表获取用户收藏的当前商品的记录
            $favorite = $user->favoriteProducts()->find($product->id);
        }
        $reviews = $product->getReview();     //商品评价数据
        return view('products.show',
            [
                'product'=>$product,
                'favorite'=>$favorite,
                'reviews'=>$reviews,
                'skus'=>$sku_data['skus'],
                'unique_attr'=>$sku_data['unique_attr'],
                'select_attr'=>$sku_data['select_attr'],
                'similar' => $similarProducts, //相似商品
            ]);

    }

    //商品收藏接口
    public function favor(Product $product, Request $request)
    {
        $user = $request->user();
        if ($user->favoriteProducts()->find($product->id)) {
            return [];
        }

        //向中间表添加记录
        $user->favoriteProducts()->attach($product);

        return [];
    }

    //取消商品收藏接口
    public function disfavor(Product $product, Request $request)
    {
        $user = $request->user();
        //删除中间表记录
        $user->favoriteProducts()->detach($product);

        return [];
    }

    //收藏商品列表
    public function favorites()
    {
        $products = Auth::user()->favoriteProducts()->paginate(16);
        return view('products.favorites', ['products'=>$products]);
    }
}
