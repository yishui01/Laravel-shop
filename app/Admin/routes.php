<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {
    $router->get('/', 'HomeController@index');

    //用户
    $router->resource('users', 'UsersController');
    //优惠券
    $router->resource('coupon_codes', 'CouponCodesController');
    //商品分类
    $router->resource('categories', 'CategoriesController');
    //商品列表
    $router->resource('products', 'ProductsController');
    //商品属性值列表
    $router->resource('attributes', 'AttributesController');
    //商品SKU
    $router->resource('skus', 'ProductSkusController');

    //订单列表
    $router->get('orders', 'OrdersController@index')->name('admin.orders.index');
    //下面的这订单详情页是自己实现的，自己的模板和方法
    $router->get('orders/{order}', 'OrdersController@show')->name('admin.orders.show');
    //订单发货
    $router->post('orders/{order}/ship', 'OrdersController@ship')->name('admin.orders.ship');
    //拒绝退款
    $router->post('orders/{order}/refund', 'OrdersController@handleRefund')->name('admin.orders.handle_refund');

    //商品列表API
    $router->get('api/productlist', 'ProductSkusController@getProduct')->name('admin.api.productlist');
    //商品属性API
    $router->get('api/attributes/{id}', 'ProductSkusController@getAttributes')->name('admin.api.attributes');
    //商品分类API
    $router->get('api/categories', 'CategoriesController@getTreeCateList')->name('admin.api.categories');

    //轮播图管理
    $router->resource('banners', 'BannersController');
    //站点管理
    $router->resource('web_infos', 'WebInfosController');
    //众筹商品
    $router->resource('crowdfunding_products', 'CrowdfundingProductsController');
    //秒杀商品
    $router->resource('seckill_products', 'SeckillProductsController');
});
