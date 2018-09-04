<?php

namespace App\Http\Controllers\Api\V1;

use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{
    use Helpers;

    protected $success_code          = 201; //请求成功状态码

    protected $unauth_code           = 401; //用户未授权（未登录、token失效、未注册）
    protected $forbidden_code        = 403; //请求无效，服务器拒绝执行（该状态码的错误信息需要提示给用户）
    protected $invalid_code          = 422; //请求信息存在语法错误（该状态码的错误信息需要提示给用户）
    protected $servererr_code        = 500; //服务器内部错误

    //捕获未定义异常时执行的函数
    protected function recordAndResponse(\Exception $e, $log_message, $response_message)
    {
        Log::error($log_message,['msg'=>$e->getMessage().'\n'.$e->getFile().'\n'.$e->getLine()]);
        return $this->response->error($response_message,$this->servererr_code);
    }
}
