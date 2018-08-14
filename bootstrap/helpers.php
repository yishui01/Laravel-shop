<?php

function route_class()
{
    return str_replace('.', '-', Route::currentRouteName());
}

function parse_xml($xml)
{
    // 用 simplexml_load_string 函数初步解析 XML，返回值为对象，再通过 normalize_xml 函数将对象转成数组
    return normalize_xml(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_NOCDATA | LIBXML_NOBLANKS));
}

// 将 XML 解析之后的对象转成数组
function normalize_xml($obj)
{
    $result = null;
    if (is_object($obj)) {
        $obj = (array) $obj;
    }
    if (is_array($obj)) {
        foreach ($obj as $key => $value) {
            $res = normalize_xml($value);
            if (('@attributes' === $key) && ($key)) {
                $result = $res;
            } else {
                $result[$key] = $res;
            }
        }
    } else {
        $result = $obj;
    }
    return $result;
}

//用session_key解密小程序的userinfo
function resolveMiniUserInfo($session_key, $encryptedData, $iv)
{
    $appid = env('WECHAT_MINI_PROGRAM_APPID');
    if (strlen($session_key) != 24) {
        throw new Exception('session_key 长度不对');
    }
    $aesKey=base64_decode($session_key);

    if (strlen($iv) != 24) {
        throw new Exception('iv 长度不对');
    }
    $aesIV=base64_decode($iv);

    $aesCipher=base64_decode($encryptedData);

    $result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

    $dataObj=json_decode( $result );
    if( $dataObj  == NULL )
    {
        throw new Exception('解密失败，数据为空');
    }
    if( $dataObj->watermark->appid != $appid )
    {
        throw new Exception('解密失败，appid不对');
    }
    return $result;
}

//组建一个获取用户某个表所有数据的查询构造器
function create_relation_builder($user, $data_model_name)
{
    if(!class_exists($data_model_name)) {
        //自动补全模型名称
        $data_model_name = '\App\Models\\'.$data_model_name;
    }
    $builder = $data_model_name::query();
    if ($user instanceof \App\Models\User) {
        //传递的是PC端的账号模型，找出所有绑定了这个账号的第三方账号，合并返回
        $all_user = \App\Models\SocialInfo::where('user_id', $user->id)->pluck('type', 'id');
        $builder->where(function ($query) use ($user, $all_user){
            $query->where([ //PC账号的
                ['user_id', '=', $user->id],
                ['user_type', '=', 'users']
            ]);
            foreach ($all_user as $id => $user_type) { //所有第三方账号的
                $query->orWhere([
                    ['user_id', '=', $id],
                    ['user_type', '=', $user_type]
                ]);
            }
        });

    } else if($user instanceof \App\Models\SocialInfo) {
        //传递的是第三方表的用户模型，先看有没有绑定PC端的账号，绑定了那就查出所有第三方账号与PC端账号的数据合并返回
        if ($user->user_id) {
            //如果绑定了PC端的账号,把所有的订单全部查出来返回
            $all_user = \App\Models\SocialInfo::where('user_id', $user->user_id)->pluck('type', 'id');
            $builder->where(function ($query) use ($user, $all_user){
                $query->where([ //PC账号的
                    ['user_id', '=', $user->id],
                    ['user_type', '=', 'users']
                ]);
                foreach ($all_user as $id => $user_type) {
                    $query->orWhere([ //所有第三方账号的
                            ['user_id', '=', $id],
                            ['user_type', '=', $user_type]
                    ]);
                }
            });
        } else {
            $builder->where([
                ['user_id', '=', $user->id],
                ['user_type', '=', $user->user_type]
            ]);
        }
    } else {
        \Illuminate\Support\Facades\Log::error('user模型传递错误',['user'=>$user]);
        throw new Exception('User模型错误');
    }

    return $builder;
}

//获取客户端IP
function get_client_ip(){
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
        $ip = getenv("HTTP_CLIENT_IP");
    else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
        $ip = getenv("REMOTE_ADDR");
    else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
        $ip = $_SERVER['REMOTE_ADDR'];
    else
        $ip = "unknown";
    return($ip);
}
