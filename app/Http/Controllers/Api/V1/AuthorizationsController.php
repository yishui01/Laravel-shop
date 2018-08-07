<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\MiniLoginRequest;
use App\Http\Requests\Api\V1\MiniRegisterRequest;
use App\Models\SocialInfo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
class AuthorizationsController extends Controller
{
    //小程序登录，成功返回201.其他均为失败
    public function miniProgramLogin(MiniLoginRequest $request)
    {
        $code = $request->code;
        //根据code获取微信的openid和session_key
        $mini = \EasyWeChat::miniProgram();
        $data = $mini->auth->session($code);
        // 如果结果错误，说明 code 已过期或不正确，返回 401 错误
        if (isset($data['errcode'])) {
            return $this->response->errorUnauthorized('code 不正确或已过期');
        }

        $social_info = $this->checkRegister($data['openid'], 'mini', $data['unionid'] ?? 0);

        if (!$social_info) {
            //如果该用户之前没有注册过账号，返回400，让客户端拉起授权页面
            return $this->response->error('用户还未授权','400');
        }

        //如果表中已经存在用户记录，更新session_key,返回token
        try{
            $social_info->session_key = $data['session_key'];
            $social_info->save();
            $token = Auth::guard('api')->fromUser($social_info);
            return $this->responseWithToken($token)->setStatusCode(201);
        } catch (\Exception $e) {
            Log::error('cannot create  mini token ',['msg'=>$e->getMessage().'\n'.$e->getFile().'\n'.$e->getLine()]);
            return $this->response->error('server error','500');
        }

    }

    //小程序用户授权后的回调接口，注册用户信息到第三方用户表中，成功返回201，其他均为失败
    public function miniProgramStore(MiniRegisterRequest $request)
    {
        $mini = \EasyWeChat::miniProgram();
        $data = $mini->auth->session($request->code);
        // 如果结果错误，说明 code 已过期或不正确，返回 401 错误
        if (isset($data['errcode'])) {
            return $this->response->errorUnauthorized('code 不正确或已过期');
        }

        try{
            //解密数据->检查是否注册->?入库->返回token
            $info = resolveMiniUserInfo($data['session_key'], $request->encryptedData, $request->iv);
            $info = json_decode($info);
            //看下这个openid或者unionid有没有被注册,如果已经注册了，直接返回，没有就注册再返回
            $social_info = $this->checkRegister($data['openid'], 'mini', $data['unionid'] ?? 0);
            if (!$social_info) {
                //未注册时注册新用户
                $social_info = SocialInfo::create([
                    'openid'      => $info->openId,
                    'unionid'     => $info->unionId ?? null,
                    'session_key' => $data['session_key'],
                    'type'        => 'mini',
                    'avatar'      => $info->avatarUrl,
                    'nickname'    => $info->nickName,
                    'gender'      => $info->gender,
                    'user_id'     => null,
                    'extra'       => json_encode($info)
                ]);
            }

            try {
                $token = Auth::guard('api')->fromUser($social_info);
                return $this->responseWithToken($token)->setStatusCode(201);
            } catch (\Exception $e) {
                Log::error('cannot create  mini token ',['msg'=>$e->getMessage().'\n'.$e->getFile().'\n'.$e->getLine()]);
                return $this->response->error('server error','500');
            }

        }catch (\Exception $e) {
            Log::error('小程序注册失败：'.$e->getFile().$e->getLine().$e->getMessage());
            $this->response->error('获取用户信息失败', '400');
        }

    }

    //返回生成token的响应
    public function responseWithToken($token)
    {
        return $this->response->array([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL()
        ]);
    }

    //校验小程序的token是否有效，返回201状态码代表有效，其他的全部为无效token
    public function miniCheckToken()
    {
        try{
            $user = Auth::guard('api')->user(); //根据token解析出对应的用户
            if (!$user) {
                throw new \Exception('Token无效');
            }
        }catch (\Exception $e) {
            //token无效
            $this->response->error('token已经失效', 400);
        }
        $this->response->error('',201);
    }

    //检查第三方用户是否注册，注册了返回已有的user信息，否则返回null
    public function checkRegister($openid = 0,$type = '', $unionid = 0)
    {
        $social_info = null;
        //如果获取到了unionid或者openid,查找对应的用户是否存在
        if (isset($unionid) && !empty($unionid)) {
            //如果有unionid，直接用unionid查找是否有该用户
            $social_info = SocialInfo::where('unionid', $unionid)
                ->where(function ($query){
                    $query->where('type', 'wechat')
                        ->orWhere('type', 'mini');
                })->first();
        } else if(isset($openid) && !empty($openid)) {
            $social_info = SocialInfo::where('openid', $openid)->where('type', $type)->first();
        }
        return $social_info;
    }
}
