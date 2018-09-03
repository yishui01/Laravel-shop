<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

class InvalidRequestException extends Exception
{
    public function __construct(string $message = "", int $code = 400)
    {
        parent::__construct($message, $code);
    }

    //Laravel 5.5 之后支持在异常类中定义 render() 方法，该异常被触发时系统会调用 render() 方法来输出
    public function render(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(['msg'=>$this->message], $this->code);
        }
        
        return view('pages.error', ['msg'=>$this->message]);
    }
}
