<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\InvalidRequestException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CaptchaService;
use App\Services\SmsService;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use App\Http\Requests\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    //显示重置密码第一个表单，要求输入手机号
    public function showSendSmsForm()
    {
        return view('auth.my.send_reset_sms');
    }

    //检查验证码是否正确，发送找回密码短信
    public function sendResetSms(Request $request, CaptchaService $captchaService, SmsService $smsService)
    {
        $this->validate($request, [
            'ticket'  => 'required|string',
            'randstr' => 'required|string',
            'phone'   => ['required','string','between:11,11',function($attr, $val,$fail) {
            if (!User::where('phone', $val)->exists())return $fail('手机号不存在');
            }],
        ]);
        $res_verify = $captchaService->verify($request->ticket, $request->randstr);
        if (!$res_verify)throw new InvalidRequestException('验证码不正确');
        //发送短信
        $sms = $smsService->sendSms($request->phone);
        return redirect()->route('sms.password.resetform', ['key'=>$sms['key'], 'phone'=>$request->phone])
            ->with('message', '验证码已发送到您的手机，请查收手机短信，并填写短信中的验证码（此验证码15分钟内有效）');
    }

    //显示重置密码表单
    public function showResetForm(Request $request)
    {
        return view('auth.my.resetpassword', ['key'=>$request->key, 'phone'=>$request->phone]);
    }

    //验证手机验证码，重置密码
    public function smsResetPassword(Request $request)
    {
        //验证code是否正确
        $cacheData = \Cache::get($request->key);
        $this->validate($request, [
            'key' => 'required|string',
            'code' => ['required','string', function($attribute, $value, $fail) use ($cacheData) {
                if (!$cacheData) {
                    return  $fail('手机验证码已经失效，请重新开始重置密码流程');
                }
                if (!hash_equals((string)$cacheData['code'], $value)) {
                    return  $fail('手验证码错误，请重新输入');
                }
            }],
            'phone'=>['required','string', function($attribute, $value, $fail) use ($cacheData){
                if (!hash_equals((string)$cacheData['phone'], $value)) {
                    return  $fail('验证码和手机号不符');
                }
            }],
            'password' => 'required|string|min:6|confirmed'
        ]);

        //更新用户密码
        $user = User::where('phone', $request->phone)->firstOrFail();
        $user->password = Hash::make($request->password);
        $user->setRememberToken(Str::random(60));

        $user->save();

        // 清除验证码缓存
        \Cache::forget($request->key);

        event(new PasswordReset($user));

        Auth::guard()->login($user);

        return redirect()->route('products.index')->with('success','重置密码成功！^_^');
    }



    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = $this->broker()->sendResetLink(
            $request->only('email')
        );

        return $response == Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($response)
            : $this->sendResetLinkFailedResponse($request, $response);
    }

    /**
     * Validate the email for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateEmail(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);
    }

    /**
     * Get the response for a successful password reset link.
     *
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkResponse($response)
    {
        return back()->with('status', trans($response));
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return back()->withErrors(
            ['email' => trans($response)]
        );
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker();
    }
}
