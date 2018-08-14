<?php

namespace App\Http\Requests\Api\V1;

class MiniLoginRequest extends ApiBaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'code'=>'required|string'
        ];
    }
}
