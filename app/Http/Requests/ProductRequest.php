<?php

namespace App\Http\Requests;
use Illuminate\Validation\Rule;
class ProductRequest extends Request
{

    public function rules()
    {
        return [
            'title'=>['required', Rule::unique('products')->ignore(request()->id)],
            'description' => ['required', 'string'],
            'image' =>['required'],
            'category_id'=>['required']
        ];
    }

    public function attributes()
    {
        return [
            'category_id' =>'分类名称',
            'image' =>'封面图片'
        ];
    }
}
