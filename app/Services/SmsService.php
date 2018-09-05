<?php

namespace App\Services;

use Overtrue\EasySms\EasySms;

class SmsService
{
    //发送短信,返回缓存键值和持续时间
    public function sendSms($phone = '')
    {
        if (!$phone)return false;
        $config = config('easysms');
        $send_config =  $config['send_config'];
        $sms_config  =  $config['sms_config'];
        if (env('APP_DEBUG')) {
            $code = 1234;
        } else {
            $easySms = new EasySms($send_config);
            $code = $this->makeRandCode();
            $easySms->send($phone, [
                'content'  => '您的验证码为: ',
                'template' => $sms_config['template_id'],
                'data' => [
                    'data1' => $code,
                    'data2' => $sms_config['expire'],
                ],
            ]);
        }
        $key = 'verificationCode_'.str_random(15);
        $expiredAt = now()->addMinutes($sms_config['expire']);
        \Cache::put($key, ['phone' => $phone, 'code' => $code], $expiredAt);
        return ['key'=>$key, 'expire'=>$expiredAt];
    }

    //随机生成短信验证码
    public function makeRandCode()
    {
        // 生成4位随机数，左侧补0
        return random_int(1000,9999);
    }
}