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

    });
    // 结束
});

Route::get('products', 'ProductsController@index')->name('products.index');
Route::get('products/{product}', 'ProductsController@show')->name('products.show'); //这个要放后面不然会和收藏商品列表冲突
