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

    public function create()
    {
        return view('user_addresses.create_and_edit', ['address' => new UserAddress()]);
    }

    public function store(UserAddressRequest $request) {
        $user_address = new UserAddress();
        $user_address->fill($request->all());
        $user_address->user_id = Auth::id();
        $user_address->save();
        return redirect()->route('user_addresses.index')->with('success', '地址添加成功！');
    }
}
