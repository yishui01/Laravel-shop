<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCartRequest;
use App\Models\CartItem;
use App\Models\ProductSku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    //添加商品到购物车
    public function add(AddCartRequest $request)
    {
        $user = Auth::user();
        $sku_id = $request->input('sku_id');
        $amount = $request->input('amount');
        if ($cart = $user->cartItems()->where('product_sku_id', $sku_id)->first()) {
            //如果之前购物车内有这个skuid，那么把数量加上去
            $cart->amount += $amount;
        } else {
            $cart = new CartItem();
            $cart->user_id = Auth::id();
            $cart->product_sku_id = $request->sku_id;
            $cart->fill($request->all());
        }
        $cart->save();
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
        return CartItem::where([
            ['product_sku_id', $sku->id],
            ['user_id', Auth::id()],
        ])->delete();
        return [];
    }
}
