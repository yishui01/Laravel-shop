<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCartRequest;
use App\Models\CartItem;
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
}
