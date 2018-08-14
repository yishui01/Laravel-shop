<?php

namespace App\Http\Requests\Api\V1;


class MiniRegisterRequest extends ApiBaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'code'=>'required|string',      //wx.login()的code，用于获取session_key
            'encryptedData'=>'required',   //getUserinfo获取到的信息，需要sesison_key解密
            'iv'=>'required',              //解密时需要的数据
        ];
    }
}
