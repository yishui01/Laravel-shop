<?php

namespace App\Services;

use GuzzleHttp\Client;

/**
 * 查询腾讯云验证码是否正确
 *
 * Class CaptchaService
 * @package App\Services
 */
class CaptchaService
{
    protected $tx_captcha_url = 'https://ssl.captcha.qq.com/ticket/verify';

    public function verify($ticket = '', $randstr = '')
    {
        $query = [
            'aid'          => env('TX_CAPTCHA_APPID'),
            'AppSecretKey' => env('TX_CAPTCHA_APPKEY'),
            'Ticket'       => $ticket,
            'Randstr'      => $randstr,
            'UserIP'       => $_SERVER['REMOTE_ADDR'],
        ];
        $http_query = http_build_query($query);
        $url = ($this->tx_captcha_url).'?'.$http_query;
        $client = new Client([
            'base_uri' => $url,
            'timeout'  => 2.0
        ]);
        $response = $client->request('GET');
        $body = $response->getBody()->getContents();
        $body = json_decode($body, true);
        if (isset($body['response']) && $body['response'] == 1) {
            return true;
        }
        return false;
    }
}