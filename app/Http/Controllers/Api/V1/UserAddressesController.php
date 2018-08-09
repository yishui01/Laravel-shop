<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class UserAddressesController extends Controller
{
    public function index()
    {
        $user = Auth::guard('api')->user(); //根据token解析出对应的用户
        $user_address = ['123','456',];
        return $this->response->array($user_address)->setStatusCode(201);
    }
}
