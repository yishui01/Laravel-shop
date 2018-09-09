<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
$api = app('Dingo\Api\Routing\Router');

$api->version('v1', [
    'namespace' => 'App\Http\Controllers\Api\V1',
    'middleware' => ['bindings'], //bindings中间件是为了隐式注入模型
], function ($api) {
    $api->group([
        'middleware' => ['api.throttle'],
        'prefix'     => 'mini'
    ], function ($api) {
        //网站基本信息
        $api->get('web_info', 'WebInfosController@index')
            ->name('api.mini.web_infos');
        // 小程序登录
        $api->post('authorizations', 'AuthorizationsController@miniLogin')
            ->name('api.mini.authorizations.store');
        //小程序授权回调注册
        $api->post('register', 'AuthorizationsController@miniRegister')
            ->name('api.mini.authorizations.store');
        //验证小程序token是否有效
        $api->post('checkToken', 'AuthorizationsController@miniCheckToken')
            ->name('api.mini.authorizations.checkToken');
        //轮播图列表接口
        $api->get('banners', 'BannersController@index')
            ->name('api.mini.banners.index');
        //分类列表接口
        $api->get('categories', 'CategoriesController@index')
            ->name('api.mini.categories.index');
        //优惠券列表
        $api->get('coupon_codes','CouponCodesController@index')
            ->name('api.mini.coupon_codes.index');
        //商品列表
        $api->get('products', 'ProductsController@index')
            ->name('api.mini.products.index');
        //商品详情
        $api->get('products/{product}', 'ProductsController@show')
            ->name('api.mini.products.index');
        //商品SKU详情
        $api->get('skus/{product_sku}', 'ProductsController@sku')
            ->name('api.mini.products.sku');

        $api->group(['middleware'=>['api.auth']], function ($api) {
            //领取优惠券
            $api->post('coupon_codes', 'CouponCodesController@receive')
                ->name('api.coupon_codes.receive');
            //获取登录用户的收货地址列表
            $api->get('user_address', 'UserAddressesController@index')
                ->name('api.user_address.index');
            //获取用户收货地址详情
            $api->get('user_address/{user_address}', 'UserAddressesController@show')
                ->name('api.user_address.show');
            //添加用户收货地址
            $api->post('user_address', 'UserAddressesController@store')
                ->name('api.user_address.store');
            //修改用户收货地址
            $api->put('user_address/{user_address}', 'UserAddressesController@update')
                ->name('api.user_address.update');
            //删除用户收货地址
            $api->delete('user_address/{user_address}', 'UserAddressesController@destroy')
                ->name('api.user_address.destroy');

            //下单
            $api->post('orders', 'OrdersController@store')
                ->name('api.orders.store');
            //取消订单（关闭订单）
            $api->delete('orders/{order}', 'OrdersController@destroy')
                ->name('api.orders.destroy');
            //获取用户的订单列表页信息
            $api->get('orders', 'OrdersController@index')
                ->name('api.orders.index');
            //获取某个订单详情
            $api->get('orders/{order}', 'OrdersController@show')
                ->name('api.orders.show');
            //小程序微信支付 //微信支付回调通知url以及退款都在web.php中定义，与web端共用一套路由
            $api->get('payment/wechat/{order}', 'PaymentController@miniPayByWechat')
                ->name('api.payement.wechat');
        });
    });


});

