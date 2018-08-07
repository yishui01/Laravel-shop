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
    //'middleware' => ['serializer:array','bindings', 'change-locale'], //这个中间件可以将DataArraySerializer转换成ArraySerializer，少一层嵌套结构包裹
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
    });


});

