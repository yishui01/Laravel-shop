<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCartRequest;
use App\Models\CartItem;
use App\Models\ProductSku;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService$cartService)
    {
        $this->cartService = $cartService;
    }

    //添加商品到购物车
    public function add(AddCartRequest $request)
    {
        $this->cartService->add($request->input('sku_id'), $request->input('amount'));
        return [];
    }

    //查看购物车
    public function index()
    {
        /**
         * 这里的关联关系用.符号来表示，具体含义如下：
         * 购物车模型是与sku表有一个productSku方法产生关联，但是并没有与product表产生关联
         * 所以要通过productSku模型定义的product方法才能与product模型产生关联，
         * 这里的.符号就可以达到多级关联的效果
         */
        $cartItems = Auth::user()->cartItems()->with('productSku.product')->paginate(16);
        $addresses = Auth::user()->addresses()->orderBy('last_used_at', 'desc')->get();
        return view('cart.index', ['cartItems' => $cartItems, 'addresses' => $addresses]);
    }

    //从购物车中移除商品
    public function remove(ProductSku $sku)
    {
        $this->cartService->remove($sku->id);
        return [];
    }
}
