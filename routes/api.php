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
        'middleware' => 'api.throttle',
    ], function ($api) {
        // 小程序登录
        $api->post('mini/authorizations', 'AuthorizationsController@miniProgramLogin')
            ->name('api.mini.authorizations.store');
        //小程序授权回调注册
        $api->post('mini/register', 'AuthorizationsController@miniProgramStore')
            ->name('api.mini.authorizations.store');
        //验证小程序token是否有效
        $api->post('mini/checkToken', 'AuthorizationsController@miniCheckToken')
            ->name('api.mini.authorizations.checkToken');
        //轮播图列表接口
        $api->get('mini/banners', 'BannersController@index')
            ->name('api.mini.banners.index');
        //分类列表接口
        $api->get('mini/categories', 'CategoriesController@index')
            ->name('api.mini.categories.index');
        //商品列表
        $api->get('mini/products', 'ProductsController@index')
            ->name('api.mini.products.index');
        //商品详情
        $api->get('mini/products/{product}', 'ProductsController@show')
            ->name('api.mini.products.index');
        //商品SKU详情
        $api->get('mini/skus/{product_sku}', 'ProductsController@sku')
            ->name('api.mini.products.sku');

        $api->group(['middleware'=>'api.auth'], function ($api) {
            //获取登录用户的地址
            $api->get('mini/user_address', 'UserAddressesController@index')
                ->name('api.user_address.index');
        });
    });


});

