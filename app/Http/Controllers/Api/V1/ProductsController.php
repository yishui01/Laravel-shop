<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Product;
use App\Models\ProductSku;
use App\Transformers\ProductTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\InvalidRequestException;
class ProductsController extends Controller
{
    //商品列表接口
    public function index(Request $request)
    {
        $product = new Product();
        $query = $product->query()->has('skus', '>=', 1);

        $query->where('on_sale',1);
        if ($categoryId = $request->category_id) {
            $query->where('category_id', $categoryId);
        }

        if ($title = $request->title) {
            $query->where('title', 'like','%'.$request->title.'%');
        }

        $products = $query->paginate(200);

        return $this->response->paginator($products, new ProductTransformer());
    }

    //商品详情接口
    public function show(Product $product,Request $request)
    {

        if (!$product || !$product->on_sale) {
            throw new InvalidRequestException('该商品未上架');
        }

        //处理商品的图片路径，api隐式注入的模型不会触发获取器
        $product->image = strpos($product->image, 'http') === false ? env('APP_URL').'/uploads/'.$product->image : $product->image;

        $sku_data = $product->getSkuDetail(); //sku以及属性数据

        $favorite = false;                    //是否收藏了该商品

        if ($user = Auth::guard('api')->user()){ //根据token解析出对应的用户
            //从中间表获取用户收藏的当前商品的记录
            $favorite = $user->favoriteProducts()->find($product->id);
        }
        $reviews = $product->getReview();     //商品评价数据
        $data = [
            'sku'=>$sku_data,
            'favorite'=>$favorite,
            'reviews'=>$reviews,
            'product'=>$product
        ];
        return $this->response->array($data)->setStatusCode(201);
    }

    //商品SKU接口
    public function sku(ProductSku $productSku)
    {
        return response($productSku->load('product'), 201);
    }


}
