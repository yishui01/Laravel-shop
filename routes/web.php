<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//秒杀商品下单
Route::post('seckill_orders', 'OrdersController@seckill')->name('seckill_orders.store')->middleware('random_drop:50');

Route::redirect('/', '/products')->name('root');

//用户登录注册
Auth::routes();

//登录
$this->get('login', 'Auth\LoginController@showLoginForm')->name('login');
$this->post('login', 'Auth\LoginController@login');
$this->post('logout', 'Auth\LoginController@logout')->name('logout');

//重写注册为手机短信
Route::get('register', 'Auth\RegisterController@showPart1')->name('register');
Route::post('checkCaptcha', 'Auth\RegisterController@sendSms')->name('register.checkCaptcha');
Route::get('register2', 'Auth\RegisterController@showPart2')->name('register2');
Route::post('register3', 'Auth\RegisterController@showPart3')->name('register3');

//重写忘记密码为手机短信验证,并且保留原来的邮箱找回逻辑和路由
Route::prefix('sms')->group(function () {
    $this->get('password/sms', 'Auth\ForgotPasswordController@showSendSmsForm')
        ->name('sms.password.sms'); //显示填写手机号表单
    $this->post('password/check', 'Auth\ForgotPasswordController@sendResetSms')
        ->name('sms.password.check'); //对比验证码，发送手机短信
    $this->get('password/reset', 'Auth\ForgotPasswordController@showResetForm')
        ->name('sms.password.resetform'); //显示重置新密码表单
    $this->post('password/reset', 'Auth\ForgotPasswordController@smsResetPassword')
        ->name('sms.password.reset'); //更新用户新密码
});


Route::group(['middleware' => ['my_auth']], function() {
    //未验证邮箱的重定向页面
    Route::get('/email_verify_notice', 'PagesController@emailVerifyNotice')->name('email_verify_notice');
    //手动发送验证以邮件页面
    Route::get('/email_verification/send', 'EmailVerificationController@send')->name('email_verification.send');
    //验证邮箱页面
    Route::get('/email_verification/verify', 'EmailVerificationController@verify')->name('email_verification.verify');

    //收货地址
    Route::resource('user_addresses', 'UserAddressesController');
    //收藏商品和取消收藏
    Route::post('products/{product}/favorite', 'ProductsController@favor')->name('products.favor');
    Route::delete('products/{product}/favorite', 'ProductsController@disfavor')->name('products.disfavor');
    //收藏商品列表
    Route::get('products/favorites', 'ProductsController@favorites')->name('products.favorites');
    //将商品添加到购物车
    Route::post('cart', 'CartController@add')->name('cart.add');
    //购物车列表
    Route::get('cart', 'CartController@index')->name('cart.index');
    //从购物车中移除
    Route::delete('cart/{sku}', 'CartController@remove')->name('cart.remove');
    //购物车下单
    Route::post('orders', 'OrdersController@store')->name('orders.store');
    //订单列表页面
    Route::get('orders/index', 'OrdersController@index')->name('orders.index');
    //订单详情页面
    Route::get('orders/{order}', 'OrdersController@show')->name('orders.show');
    //支付宝扫码支付
    Route::get('payment/{order}/alipay', 'PaymentController@payByAlipay')->name('payment.alipay');
    //微信扫码支付
    Route::get('payment/{order}/wechat', 'PaymentController@payByWechat')->name('payment.wechat');
    //用户确认收货
    Route::post('orders/{order}/received', 'OrdersController@received')->name('orders.received');
    //发布评价标表单
    Route::get('orders/{order}/review', 'OrdersController@review')->name('orders.review.show');
    //存储评价
    Route::post('orders/{order}/review', 'OrdersController@sendReview')->name('orders.review.store');
    //申请退款
    Route::post('orders/{order}/apply_refund', 'OrdersController@applyRefund')->name('orders.apply_refund');
   //优惠券查询
    Route::get('coupon_codes/{code}', 'CouponCodesController@show')->name('coupon_codes.show');
    //众筹商品下单
    Route::post('crowdfunding_orders', 'OrdersController@crowdfunding')->name('crowdfunding_orders.store');
    //创建分期付款
    Route::post('payment/{order}/installment', 'PaymentController@payByInstallment')->name('payment.installment');
    //分期付款列表
    Route::get('installments', 'InstallmentsController@index')->name('installments.index');
    //分期付款详情页
    Route::get('installments/{installment}', 'InstallmentsController@show')->name('installments.show');
    //分期付款支付宝拉起支付
    Route::get('installments/{installment}/alipay', 'InstallmentsController@payByAlipay')->name('installments.alipay');
    //分期付款前端回调
    Route::get('installments/alipay/return', 'InstallmentsController@alipayReturn')->name('installments.alipay.return');
    //分期付款拉起微信扫码支付
    Route::get('installments/{installment}/wechat', 'InstallmentsController@payByWechat')->name('installments.wechat');
});
//商品列表
Route::get('products', 'ProductsController@index')->name('products.index');
//商品详情,这个样放后面，不然和我的收藏冲突了
Route::get('products/{product}', 'ProductsController@show')->name('products.show'); //这个要放后面不然会和收藏商品列表冲突


//支付宝扫码服务端回调
Route::post('payment/alipay/notify', 'PaymentController@alipayNotify')->name('payment.alipay.notify');
//支付宝扫码支付前端回调函数
Route::get('payment/alipay/return', 'PaymentController@alipayReturn')->name('payment.alipay.return');
//微信扫码支付回调
Route::post('payment/wechat/notify', 'PaymentController@wechatNotify')->name('payment.wechat.notify');
//微信退款通知回调
Route::post('payment/wechat/refund_notify', 'PaymentController@wechatRefundNotify')->name('payment.wechat.refund_notify');


// 分期付款支付宝后端回调
Route::post('installments/alipay/notify', 'InstallmentsController@alipayNotify')->name('installments.alipay.notify');
// 分期付款微信扫码支付后端回调
Route::post('installments/wechat/notify', 'InstallmentsController@wechatNotify')->name('installments.wechat.notify');

//分期退款微信回调地址（支付宝的不用回调，同步获取信息）
Route::post('installments/wechat/refund_notify', 'InstallmentsController@wechatRefundNotify')->name('installments.wechat.refund_notify');
