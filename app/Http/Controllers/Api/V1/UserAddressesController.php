<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\UserAddressRequest;
use App\Models\Product;
use App\Models\SocialInfo;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class UserAddressesController extends Controller
{
    //返回用户收货地址
    public function index()
    {
        $social_user = Auth::guard('api')->user(); //根据token解析出对应的用户
        //var_dump($titles = Product::pluck('键值','键名')->toArray());
        $builder = UserAddress::query();
        if ($social_user->user_id) {
            //如果绑定了PC端的账号,找出所有的第三方账号对应的地址，与PC端账号的地址一起返回
            $all_data = SocialInfo::where('user_id', $social_user->user_id)->pluck('type', 'id');

            foreach ($all_data as $id=>$user_type) {
                $builder->orWhere(function ($query) use ($id, $user_type){
                    $query->where('user_id', $id)->where('user_type', $user_type);
                });
            }

            $builder->orWhere(function ($query) use ($social_user){
                //这是PC的账号的收货地址
                $query->where('user_id', $social_user->user_id)->where('user_type', 'users');
            });
        } else {
            $builder->where('user_id', $social_user->user_id)->where('user_type', $social_user->type);
        }

        $address = $builder->get();
        foreach ($address as &$v) {
            $v->full_address = $v->FullAddress;
        }
        return $this->response->array($address)->setStatusCode(201);
    }

    //添加收货地址接口
    public function add(UserAddressRequest $request)
    {
        $user = $this->user();
        $user_address = new UserAddress();
        $user_address->fill($request->all());
        $user_address->user_id = $user->id;
        $user_address->user_type = 'mini';
        $user_address->save();
        return response('',201);
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
        return response('',201);
    }
}
