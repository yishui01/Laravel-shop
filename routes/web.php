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
Route::get('products', 'ProductsController@index')->name('products.index');
Route::get('products/{product}', 'ProductsController@show')->name('products.show');

//用户登录注册
Auth::routes();

Route::group(['middleware' => 'auth'], function() {
    //未验证邮箱的重定向页面
    Route::get('/email_verify_notice', 'PagesController@emailVerifyNotice')->name('email_verify_notice');
    //手动发送验证以邮件页面
    Route::get('/email_verification/send', 'EmailVerificationController@send')->name('email_verification.send');
    //验证邮箱页面
    Route::get('/email_verification/verify', 'EmailVerificationController@verify')->name('email_verification.verify');


    Route::group(['middleware' => 'email_verified'], function() {
        //这里的路由加入了验证邮箱中间件，必须要验证邮箱才可访问
        Route::get('user_addresses', 'UserAddressesController@index')->name('user_addresses.index');
        Route::get('user_addresses/create', 'UserAddressesController@create')->name('user_addresses.create');
        Route::post('user_addresses', 'UserAddressesController@store')->name('user_addresses.store');
        Route::get('user_addresses/{user_address}', 'UserAddressesController@edit')->name('user_addresses.edit');
        Route::put('user_addresses/{user_address}', 'UserAddressesController@update')->name('user_addresses.update');
        Route::delete('user_addresses/{user_address}', 'UserAddressesController@destroy')->name('user_addresses.destroy');
    });
    // 结束
});
