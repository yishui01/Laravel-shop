<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\MiniRegisterRequest;
use App\Http\Requests\Api\V1\MiniLoginRequest;
use App\Models\User;
use App\Transformers\UserTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthorizationsController extends Controller
{
    //小程序登录，成功返回201，其他均为失败
    public function miniLogin(MiniLoginRequest $request)
    {
        $miniProgram = \EasyWeChat::miniProgram(); // 小程序
        try {
            $data = $miniProgram->auth->session($request->code); //返回openid
        } catch (\Exception $e) {
            return $this->recordAndResponse($e, '通过code解析openid时抛出异常',
                '解析code失败');
        }

        if (isset($data['errcode'])) {
            return $this->badCodeResponse();
        }
        //查询该用户的openid或者unionid是否已经注册过
        $user = $this->checkRegister($data['openid'], 'wx_mini',
            $data['unionid'] ?? 0);
        if (!$user) {
            return $this->response->error('用户未注册，请拉起授权页面提示用户授权',
                $this->unauth_code);
        }
        //已经注册
        if ($user->status == 0) {
            return $this->response->error('该用户已被禁用', $this->forbidden_code);
        }
        try{
            $token = Auth::guard('api')->fromUser($user);
            return $this->responseWithToken($token,$user)->setStatusCode($this->success_code);
        } catch (\Exception $e) {
            return $this->recordAndResponse($e, '发放token失败',
                '验证服务器错误');
        }

    }

    //小程序用户注册接口
    public function miniRegister(MiniRegisterRequest $request)
    {
        $miniProgram = \EasyWeChat::miniProgram(); // 小程序
        try {
            $data = $miniProgram->auth->session($request->code); //返回openid
        } catch (\Exception $e) {
            return $this->recordAndResponse($e, '通过code解析openid时抛出异常',
                '解析code失败');
        }
        // 如果结果错误，说明 code 已过期或不正确，返回 401 错误
        if (isset($data['errcode'])) {
            return $this->badCodeResponse();
        }
        try{
            //解密数据->检查是否注册->?入库->返回token
            $info = resolveMiniUserInfo($data['session_key'],
                $request->encryptedData,
                $request->iv);
            $info = json_decode($info);
            //看下这个openid或者unionid有没有被注册,如果已经注册了，直接返回，没有就注册再返回
            $user = $this->checkRegister($data['openid'], 'wx_mini', $data['unionid'] ?? 0);
            if (!$user) {
                //未注册时注册新用户
                $user = User::create([
                    'wx_mini_openid'  => $info->openId,
                    'wx_unionid'      => $info->unionId ?? null,
                    'avatar'          => $info->avatarUrl,
                    'name'            => $info->nickName,
                    'extra'           => json_encode($info),
                    'email'           => '',
                    'password'       => ''
                ]);
            }

            try {
                $token = Auth::guard('api')->fromUser($user);
                return $this->responseWithToken($token,$user)->setStatusCode($this->success_code);
            } catch (\Exception $e) {
                return $this->recordAndResponse($e, '发放token失败', '验证服务器错误');
            }

        }catch (\Exception $e) {
            return $this->recordAndResponse($e, '小程序注册失败', '小程序注册失败');
        }
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
            $this->response->error('token已经失效', $this->unauth_code);
        }

        return $this->response->item($user,new UserTransformer())
            ->setStatusCode($this->success_code);
    }

    //查找第三方openid是否已经注册过了，注册了则返回user信息，否则返回null
    public function checkRegister($openid = 0,$type = '',$unionid = 0)
    {
        $user = null;
        if ($type == 'wx_mini' || $type == 'wx_web') {
            //如果是小程序或者公众号登录
            if ($type == 'wx_mini') {
                $key = 'wx_mini_openid';
            } elseif ($type == 'wx_web') {
                $key = 'wx_web_openid';
            }
            if ($unionid) {
                //如果传入了unionid则先找unionid
                $user = User::where('wx_unionid', $unionid)->first();
                if ($user) {
                    //如果找到了，那就准备直接返回了，如果对应的openid为空
                    if (empty($user->$key)) {
                        $user->$key = $openid;
                        $user->save();
                    }
                    return $user;
                }
            }

            //没有通过unionid找到用户，那就再用openid找一次
            $user = User::where($key, $openid)->first();
        }

        return $user;
    }

    //登录成功，返回token和用户信息
    protected function responseWithToken($token, $user)
    {
        return $this->response->array([
            'access_token'=> $token,
            'token_type'  => 'Bearer',
            'user'        => [
                'name'          => $user->name,
                'email'         => $user->email,
                'avatar'       => $user->avatar,
                'phone'         => $user->phone,
                ]
        ]);
    }

    //无效code
    public function badCodeResponse()
    {
        return $this->response->error('code不正确或者已过期', $this->servererr_code);
    }


}
