<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\UserAddressRequest;
use App\Models\Product;
use App\Models\User;
use App\Models\UserAddress;
use App\Transformers\UserAddressTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class UserAddressesController extends Controller
{
    //返回用户收货地址列表
    public function index()
    {
        $user = $this->user(); //根据token解析出对应的用户
        $address = UserAddress::where('user_id', $user->id)
            ->orderBy('last_used_at', 'desc')->get();
        foreach ($address as &$v) {
            $v->full_address = $v->FullAddress;
        }
        return $this->response->collection($address, new UserAddressTransformer())
            ->setStatusCode($this->success_code);
    }

    //收货地址详情
    public function show(UserAddress $user_address)
    {
        return $this->response->item($user_address, new UserAddressTransformer())
            ->setStatusCode($this->success_code);
    }

    //添加收货地址接口
    public function store(UserAddressRequest $request)
    {
        $user = $this->user();
        $user_address = new UserAddress();
        $user_address->fill($request->all());
        $user_address->user_id = $user->id;
        $user_address->save();
        return $this->response->item($user_address, new UserAddressTransformer())
            ->setStatusCode($this->success_code);
    }

    //修改收货地址接口
    public function update(UserAddress $user_address, UserAddressRequest $request)
    {
        $this->authorize('update', $user_address);
        $user_address->update($request->only([
            'province',
            'city',
            'district',
            'address',
            'zip',
            'contact_name',
            'contact_phone',
        ]));
        return $this->response->noContent()->setStatusCode($this->success_code);
    }

    //删除收货地址
    public function destroy(UserAddress $user_address)
    {
        $this->authorize('update', $user_address);
        $user_address->delete();
        return $this->response->noContent()->setStatusCode($this->success_code);
    }
}
