<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Models\Attribute;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        // 创建一个查询构造器
        $builder = Product::query()->where('on_sale', true);
        // 判断是否有提交 search 参数，如果有就赋值给 $search 变量
        // search 参数用来模糊搜索商品
        if ($search = $request->input('search', '')) {
            $like = '%'.$search.'%';
            // 模糊搜索商品标题、商品详情、SKU 标题、SKU描述
            $builder->where(function ($query) use ($like) {
                $query->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhereHas('skus', function ($query) use ($like) {
                        $query->where('title', 'like', $like)
                            ->orWhere('description', 'like', $like);
                    });
            });
        }

        // 是否有提交 order 参数，如果有就赋值给 $order 变量
        // order 参数用来控制商品的排序规则
        if ($order = $request->input('order', '')) {
            // 是否是以 _asc 或者 _desc 结尾
            if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                // 如果字符串的开头是这 3 个字符串之一，说明是一个合法的排序值
                if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
                    // 根据传入的排序值来构造排序参数
                    $builder->orderBy($m[1], $m[2]);
                }
            }
        }

        $products = $builder->paginate(16);

        return view('products.index', [
            'products' => $products,
            'filters'  => [
                'search' => $search,
                'order'  => $order,
            ],
        ]);
    }

    //商品详情
    public function show(Product $product)
    {
        if (!$product || !$product->on_sale) {
            throw new InvalidRequestException('该商品未上架');
        }
        //现有的sku
        $skus = $product->skus()->get();
        //现有的唯一属性
        $unique_attr = $product->pro_attr()->where('hasMany', '0')->get();
        $select_attr = [];
        //现有的可选属性要根据已存在的skus反推出来，不能直接查询，否则会有BUG,商品属性值表没有删除的操作
        //一旦录入错误的sku，删除时不会连带删除属性值表，也就是说错误的属性值还保留在表内，所以不能通过已有
        //属性名字来直接查询出所有属性名下的所有属性值
        if ($skus) {
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
                            $select_attr[$v['attr']['id']] = ['name'=>$v['attr']['name'], 'data'=>[$v->toArray()]];
                        }
                    }
                }
            }

        } else {
            throw new InvalidRequestException('该商品没有库存啦');
        }

        $favorite = false;
        if ($user = Auth::user()) {
            //从中间表获取用户收藏的当前商品的记录
            $favorite = $user->favoriteProducts()->find($product->id);
        }
        $reviews = OrderItem::query()
            ->with(['order.user'])
            ->where('product_id', $product->id)
            ->whereNotNull('reviewed_at')
            ->orderBy('reviewed_at', 'desc')
            ->limit(10)->get();
        return view('products.show', compact('product', 'favorite','reviews', 'skus','unique_attr', 'select_attr'));

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
