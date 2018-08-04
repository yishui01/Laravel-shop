<?php

namespace App\Http\Controllers;

use App\Notifications\EmailVerificationNotification;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use App\Exceptions\InvalidRequestException;


class EmailVerificationController extends Controller
{
    //验证邮箱
    public function verify(Request $request) {
        //从url中获取email和token两个参数
        $email = $request->input('email');
        $token = $request->input('token');
        //如果有一个为空则抛出异常
        if (empty($email) || empty($token)) {
            throw new InvalidRequestException('验证链接不正确');
        }
        //从缓存中获取数据，与传过来的key（emial），value（token）比对
        $perfix = Config::get('myconfig.perfix.verify_email');
        $key = $perfix.$email;
        $value = Cache::get($key);
        if ($value != $token) {
            throw new InvalidRequestException('验证链接不正确');
        }
        //在数据表中根据邮箱查找对应的用户
        $user =User::where('email', $email)->first();
        if (!$user) {
            throw new InvalidRequestException('用户不存在');
        }
        $user->email_verified = 1;
        $user->save();
        //清空缓存
        Cache::forget($key);
        // 最后告知用户邮箱验证成功。
        return view('pages.success', ['msg' => '邮箱验证成功']);

    }

    //手动发送激活邮件（网络阻塞用户未收到邮件可以触发该方法手动发送）
    public function send(Request $request)
    {
        $user = Auth::user();
        if ($user->email_verified) {
            throw new InvalidRequestException('您已经验证过邮箱了');
        }
        //调用notify方法来发送已经定义好的通知类
        $res = $user->notify(new EmailVerificationNotification());

        return view('pages.success', ['msg' => '邮件发送成功']);
    }
}
