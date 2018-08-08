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

//首页
Route::redirect('/', '/products')->name('root');


//用户登录注册
Auth::routes();
Route::get('alipay', function() {
    return app('alipay')->web([
        'out_trade_no' => time(),
        'total_amount' => '1',
        'subject' => 'test subject - 测试',
    ]);
});
Route::group(['middleware' => 'auth'], function() {
    //未验证邮箱的重定向页面
    Route::get('/email_verify_notice', 'PagesController@emailVerifyNotice')->name('email_verify_notice');
    //手动发送验证以邮件页面
    Route::get('/email_verification/send', 'EmailVerificationController@send')->name('email_verification.send');
    //验证邮箱页面
    Route::get('/email_verification/verify', 'EmailVerificationController@verify')->name('email_verification.verify');

    //开始
    Route::group(['middleware' => 'email_verified'], function() {
        //这里的路由加入了验证邮箱中间件，必须要验证邮箱才可访问
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
        //支付宝web端扫码支付
        Route::get('payment/{order}/alipay', 'PaymentController@payByAlipay')->name('payment.alipay');
        //支付宝扫码支付前端回调函数
        Route::get('payment/alipay/return', 'PaymentController@alipayReturn')->name('payment.alipay.return');
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
        //微信退款通知回调
        Route::post('payment/wechat/refund_notify', 'PaymentController@wechatRefundNotify')->name('payment.wechat.refund_notify');
        //优惠券查询
        Route::get('coupon_codes/{code}', 'CouponCodesController@show')->name('coupon_codes.show');

    });
    // 结束
});

//支付宝扫码回调
Route::post('payment/alipay/notify', 'PaymentController@alipayNotify')->name('payment.alipay.notify');
//微信扫码支付回调
Route::post('payment/wechat/notify', 'PaymentController@wechatNotify')->name('payment.wechat.notify');

Route::get('products', 'ProductsController@index')->name('products.index');

Route::get('products/{product}', 'ProductsController@show')->name('products.show'); //这个要放后面不然会和收藏商品列表冲突
