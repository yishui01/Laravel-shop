<?php

namespace App\Services;
use App\Http\Requests\Request;
use App\Models\Order;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    private $wechat_unify_url = 'https://api.mch.weixin.qq.com/pay/unifiedorder'; //微信支付统一下单API

    //小程序微信支付调用统一下单API，并返回微信小程序拉起支付所需要的参数
    public function miniPayByWechat(Order $order, User $user)
    {

        $config = config('pay.mini'); //获取已有的微信配置信息
        $client = new Client(['timeout'=>2.0]); //设置超时时间
        //$noce_str = 'd8b1033b1b4616b6d32b07da2ef64863';
        $noce_str = md5(mt_rand(1, 9999999).microtime());//随机数
        $notify_url = route($config['notify_url_route_name']); //服务端回调地址
        $client_ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

         $attach = 'test'; //附加数据
         $body = 'testproduct';//商品描述
         $data = [
            'appid'     => $config['app_id'],
            'attach'    => $attach,
            'body'      => $body,
            'mch_id'    => $config['mch_id'],
            'nonce_str'  => $noce_str, //随机数
            'notify_url'=> $notify_url, //服务端通知地址,
            'openid'    => $user->wx_mini_openid,//小程序用户openid
            'out_trade_no'=> $order->no,//商户订单号
            'spbill_create_ip'=> $client_ip,
            'total_fee' => $order->total_amount * 100, //订单总金额（微信的单位是分）
            'trade_type'=>'JSAPI'
         ];

         $sign = $this->setSign($data, $config['key']); //签名
         $data['sign'] = $sign;
         $body = $this->translateUnifyXml($data); //将数组转换为XML字符串

         $response = $client->request('POST',$this->wechat_unify_url,[
            'body' => $body
         ]);
         $content = $response->getBody()->getContents();
         // 解析 响应xml 为数组
         $decryptedData = parse_xml($content);
         if ($decryptedData['return_code'] === 'SUCCESS') {

             if (!$this->checkSign($decryptedData, $config['key'])) {
                 throw new \Exception('微信服务器响应签名验证失败',500);
             }

             if (isset($decryptedData['result_code']) && $decryptedData['result_code'] === 'SUCCESS') {
                 //下单成功,拿到预支付标识后再次签名
                $prepay_id = $decryptedData['prepay_id']; //预支付交易会话标识,返回给小程序
                $para_arr = $this->setMiniPayParam($prepay_id, $config);
                return $para_arr;
             }

         }

         throw new \Exception('调用微信统一下单API失败：',500);

    }

    //生成微信统一下单XML
    public function translateUnifyXml($data)
    {
        $template = <<<EOL
            <xml>
               <appid>%s</appid>
               <attach>%s</attach>
               <body>%s</body>
               <mch_id>%s</mch_id>
               <nonce_str>%s</nonce_str>
               <notify_url>%s</notify_url>
               <openid>%s</openid>
               <out_trade_no>%s</out_trade_no>
               <spbill_create_ip>%s</spbill_create_ip>
               <total_fee>%u</total_fee>
               <trade_type>%s</trade_type>
               <sign>%s</sign>
            </xml>
EOL;

        return sprintf($template,
        $data['appid'],
        $data['attach'],
        $data['body'],
        $data['mch_id'],
        $data['nonce_str'],
        $data['notify_url'],
        $data['openid'],
        $data['out_trade_no'],
        $data['spbill_create_ip'],
        $data['total_fee'],
        $data['trade_type'],
        $data['sign']
        );
    }

    //生成微信小程序拉起支付所需要的参数数组
    public function setMiniPayParam($prepay_id, $config)
    {
        $data = [
            'appId'     => $config['app_id'],
            'timeStamp' => time(),
            'nonceStr'  => md5(mt_rand(1, 9999999).microtime()),
            'package'   => 'prepay_id='.$prepay_id,
            'signType'  => 'MD5'
        ];
        $sign = $this->setSign($data, $config['key']);
        $data['sign'] = $sign;
        return $data;
    }

    //生成微信支付签名
    //流程：（把数组按键名ASCII升序排列，转为http_query_string，拼接key，md5加密拼接后的字符串，再全部转为大写）
    public function setSign($data, $key)
    {
        $sign_data = [];
        foreach ($data as $k=>$v) {
            if (empty($v) || $k == 'sign')continue;
            $sign_data[$k] = $v;
        }
        ksort($sign_data); //按照键名ASCII排序
        $string_A = $this->build_http_str($sign_data);
        $string_A.='&key='.$key;
        $sign = strtoupper(md5($string_A));
        return $sign;
    }

    //拼接http字符串，http_build_query
    public function build_http_str($sign_data)
    {
        $str = '';
        $i = 0;
        foreach ($sign_data as $k=>$v) {
            if ($i == 0) {
                $tmp = $k.'='.$v;
            } else {
                $tmp = '&'.$k.'='.$v;
            }
            $i++;
            $str.=$tmp;
        }
        return $str;
    }

    //验证微信服务器响应签名是否正确，返回bool
    public function checkSign(array $decryptedData, $key)
    {
        if (!isset($decryptedData['sign']) || empty($decryptedData['sign'])) return false;
        $set_sign = $this->setSign($decryptedData, $key);
        return $decryptedData['sign'] === $set_sign;
    }

}

