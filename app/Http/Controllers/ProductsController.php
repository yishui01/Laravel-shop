<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Models\Attribute;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $page    = $request->input('page', 1) < 1 ?: 1;
        $perPage = 16;

        // 构建查询
        $params = [
            'index' => 'products',
            'type'  => '_doc',
            'body'  => [
                'from'  => ($page - 1) * $perPage, // 通过当前页数与每页数量计算偏移值
                'size'  => $perPage,
                'query' => [
                    'bool' => [
                        'filter' => [
                            ['term' => ['on_sale' => true]],
                        ],
                    ],
                ],
            ],
        ];

        // order 参数用来控制商品的排序规则
        if ($order = $request->input('order', '')) {
            // 是否是以 _asc 或者 _desc 结尾
            if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                // 如果字符串的开头是这 3 个字符串之一，说明是一个合法的排序值
                if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
                    // 根据传入的排序值来构造排序参数
                    $params['body']['sort'] = [[$m[1] => $m[2]]];
                }
            }
        }
        //通过类别来搜索
        if ($request->input('category_id') && $category = Category::find($request->input('category_id'))) {
                // 否则直接通过 category_id 筛选
                $params['body']['query']['bool']['filter'][] = ['term' => ['category_id' => $category->id]];
        }
        //通过关键字来搜索
        if ($search = $request->input('search', '')) {
            // 将搜索词根据空格拆分成数组，并过滤掉空项
            $keywords = array_filter(explode(' ', $search));

            $params['body']['query']['bool']['must'] = [];

            // 遍历搜索词数组，分别添加到 must 查询中
            foreach ($keywords as $keyword) {
                $params['body']['query']['bool']['must'][] = [
                    'multi_match' => [
                        'query'  => $keyword,
                        'fields' => [
                            'title^2',
                            'long_title^2',
                            'category^2',
                            'description',
                            'skus.title^2',
                            'skus.description',
                            'properties.value',
                        ],
                    ],
                ];
            }
        }

        $result = app('es')->search($params);

        // 通过 collect 函数将返回结果转为集合，并通过集合的 pluck 方法取到返回的商品 ID 数组
        $productIds = collect($result['hits']['hits'])->pluck('_id')->all();
        // 通过 whereIn 方法从数据库中读取商品数据
        $products = Product::query()
            ->whereIn('id', $productIds)
            ->orderByRaw(sprintf("FIND_IN_SET(id, '%s')", join(',', $productIds)))
            ->get();
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
            // 这个已经用viewcomposer自动注入了
            //// 'categoryTree' => isset($category) ? $category->getTree() : (new Category())->getTree(),
        ]);
    }

    //商品详情
    public function show(Product $product)
    {
        if (!$product || !$product->on_sale) {
            throw new InvalidRequestException('该商品未上架');
        }

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
                'select_attr'=>$sku_data['select_attr']
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
