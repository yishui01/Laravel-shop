<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {
    $router->get('/', 'HomeController@index');
    $router->resource('users', 'UsersController');
    $router->resource('products', 'ProductsController');
    $router->get('orders', 'OrdersController@index')->name('admin.orders.index');
    //下面的这订单详情页是自己实现的，自己的模板和方法
    $router->get('orders/{order}', 'OrdersController@show')->name('admin.orders.show');
    //订单发货
    $router->post('orders/{order}/ship', 'OrdersController@ship')->name('admin.orders.ship');
    //拒绝退款
    $router->post('orders/{order}/refund', 'OrdersController@handleRefund')->name('admin.orders.handle_refund');
    //优惠券列表
    $router->resource('coupon_codes', 'CouponCodesController');
    //商品列表API
    $router->get('api/productlist', 'ProductSkusController@getProduct')->name('admin.api.productlist');
    //商品属性API
    $router->get('api/attributes/{id}', 'ProductSkusController@getAttributes')->name('admin.api.attributes');

    //$router->resource('skus', 'ProductSkusController');
    //商品SKU列表
    $router->get('skus', 'ProductSkusController@index')->name('admin.skus.index');
    //显示创建商品SKU的表单
    $router->get('skus/create', 'ProductSkusController@create')->name('admin.skus.create');
    //创建商品SKU
    $router->post('skus/store', 'ProductSkusController@store')->name('admin.skus.store');
    //显示编辑商品SKU表单
    $router->get('skus/{product_sku}/edit', 'ProductSkusController@edit')->name('admin.skus.edit');
    //编辑商品SKU
    $router->put('skus/{product_sku}', 'ProductSkusController@update')->name('admin.skus.update');
    //删除商品SKU
    $router->delete('skus/{product_sku}', 'ProductSkusController@destroy')->name('admin.skus.destroy');
});
