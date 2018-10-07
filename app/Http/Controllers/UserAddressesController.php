<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserAddress;
use App\Http\Requests\UserAddressRequest;

class UserAddressesController extends Controller
{
    public function index(Request $request)
    {
        return view('user_addresses.index', [
            'addresses'=>Auth::user()->addresses
        ]);
    }

    public function create(Request $request)
    {
        if(strpos(url()->previous(), 'products') !== false) {
            //如果是从商品详情页跳过来的
            session(['address_redirect'=>url()->previous()]);
        }
        return view('user_addresses.create_and_edit', ['address' => new UserAddress(), 'cart'=>$request->cart]);

    }

    public function store(UserAddressRequest $request)
    {
        $user_address = new UserAddress();
        $user_address->fill($request->all());
        $user_address->user_id = Auth::id();
        $user_address->save();
        $route_name = 'user_addresses.index';
        if ($request->cart) {
            $route_name = 'cart.index';
        }
        if ($redirect_url = session('address_redirect')) {
            $request->session()->forget('address_redirect'); //删除这个session
            return redirect($redirect_url)->with('success', '地址添加成功！');
        }
        return redirect()->route($route_name)->with('success', '地址添加成功！');
    }

    public function edit(UserAddress $user_address)
    {
        $this->authorize('update', $user_address);
        return view('user_addresses.create_and_edit', ['address' => $user_address]);
    }

    public function update(UserAddress $user_address, UserAddressRequest $request)
    {
        $this->authorize('update', $user_address);
        $user_address->fill($request->all());
        $user_address->save();
        return redirect()->route('user_addresses.index')->with('success', '地址修改成功！');
    }

    public function destroy(UserAddress $userAddress)
    {
        $this->authorize('update', $userAddress);
        $userAddress->delete();
        // 把之前的 redirect 改成返回空数组
        return [];
    }
}
