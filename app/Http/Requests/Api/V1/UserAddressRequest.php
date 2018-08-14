<?php

namespace App\Http\Requests\Api\V1;


class UserAddressRequest extends ApiBaseRequest
{
    public function rules()
    {
        return [
            'province'      => 'required',
            'city'          => 'required',
            'district'      => 'required',
            'address'       => 'required',
            'zip'           => 'required|numeric|max:999999',
            'contact_name'  => 'required',
            'contact_phone' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'province'      => '省',
            'city'          => '城市',
            'district'      => '地区',
            'address'       => '详细地址',
            'zip'           => '邮编',
            'contact_name'  => '姓名',
            'contact_phone' => '电话',
        ];
    }

    public function messages()
    {
        return [
            'zip.max'=>'邮编只能为六位数字'
        ];
    }


}
