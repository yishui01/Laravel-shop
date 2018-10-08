<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\InvalidRequestException;
use App\Exceptions\RegisterException;
use App\Exceptions\SystemException;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Services\CaptchaService;
use App\Services\SmsService;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use App\Http\Requests\Request;
use Illuminate\Validation\ValidationException;
class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|min:2|max:8|unique:users',
            'phone' => 'required|string|max:11|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'ticket' =>'required|string',
            'randstr' =>'required|string',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name'     => $data['name'],
            'phone'    => $data['phone'],
            'password' => bcrypt($data['password']),
            'email'    => $data['email'] ?? '',
        ]);
    }

    /**
     *显示手机号表单
     */
    public function showPart1()
    {
        return view('auth.my.sms');
    }

    //第一个表单提交到此处，验证后跳转到第二个表单
    public function sendSms(Request $request, CaptchaService $captchaService, SmsService $smsService)
    {
        $this->validate($request, [
            'phone' => 'required|string|between:11,11|regex:/^1[34578]\d{9}$/|unique:users',
            'ticket'  => 'required|string',
            'randstr' => 'required|string',
        ]);
        //验证验证码
        $verify_captcha= $captchaService->verify($request->ticket, $request->randstr);
        if (!$verify_captcha)throw new InvalidRequestException('验证码错误，请重新注册 (´ο｀*)');
        //验证看手机号有没有注册过
        if (User::where('phone', $request->phone)->exists()) {
            throw new RegisterException('您已经注册过了，请直接登录^_^');
        }
        //发送短信
        try{
            $key_and_expire = $smsService->sendSms($request->phone);
        }catch (\Exception $e) {
            $err_log = '注册时发送短信错误：';
            foreach ($e->getExceptions() as $k=>$v) {
                $err_log.=$v->getMessage();
            }
            Log::error($err_log);
            throw new SystemException('发送短信错误');
        }
        return redirect()->route('register2',['key'=>$key_and_expire['key'], 'phone'=>$request->phone]);
    }

    //显示用户信息表单
    public function showPart2(Request $request)
    {
        //显示用户信息表单
        return view('auth.my.register', ['key'=>$request->key, 'phone'=>$request->phone]);
    }

    //第二个表单提交到此处，验证手机code+注册用户
    public function showPart3(Request $request, SmsService $smsService)
    {
        //验证code是否正确
        $cacheData = Cache::get($request->key);
        $this->validate($request, [
            'key'  => 'required|string',
            'code' => ['required','string', function($attribute, $value, $fail) use ($cacheData, $request) {
                if (!$cacheData) {
                    return  $fail('手机验证码已经失效，请重新注册');
                }
                if (!hash_equals((string)$cacheData['code'], $request->code)) {
                    return  $fail('手验证码错误，请重新输入');
                }
            }],
            'name' => 'required|string|min:2|max:8|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
        $user_data = $request->all();
        $user_data['phone'] = $cacheData['phone'];
        //执行注册
        event(new Registered($user = $this->create($user_data)));
        $this->guard()->login($user);
        // 清除验证码缓存
        \Cache::forget($request->key);

        //回到首页
        return redirect()->route('products.index')
            ->with('success', '恭喜您注册成功！');
    }


    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   /* public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        $this->guard()->login($user);

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }*/

    /**
     * Get the guard to be used during registration.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }

    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        //
    }
}
