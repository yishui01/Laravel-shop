<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class MiniRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

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
