<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Throwable;

class SystemException extends Exception
{
    protected $msgForUser;

    public function __construct(string $message = "", int $code = 500, string $msgForUser = '系统内部错误')
    {
        parent::__construct($message, $code);
        $this->msgForUser = $msgForUser;
    }

    //Laravel 5.5 之后支持在异常类中定义 render() 方法，该异常被触发时系统会调用 render() 方法来输出
    public function render(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(['msg' => $this->msgForUser], $this->code);
        }

        return view('pages.error', ['msg' => $this->msgForUser]);
    }
}
