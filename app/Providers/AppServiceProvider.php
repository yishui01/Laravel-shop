<?php

namespace App\Providers;

use App\Exceptions\InvalidRequestException;
use App\Http\ViewComposers\CategoryTreeComposer;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\User;
use App\Observer\AttributeObserver;
use App\Observer\CategoryObserver;
use App\Observer\ProductObserver;
use App\Observer\ProductSkuObserver;
use App\Observer\UserObserver;
use Carbon\Carbon;
use Elasticsearch\ClientBuilder;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Monolog\Logger;
use Yansongda\Pay\Pay;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        Carbon::setLocale('zh');

        ProductSku::observe(ProductSkuObserver::class);
        Product::observe(ProductObserver::class);
        User::observe(UserObserver::class);
        Category::observe(CategoryObserver::class);
        Attribute::observe(AttributeObserver::class);
        // 当 Laravel 渲染 products.index 和 products.show 模板时，就会使用 CategoryTreeComposer 这个来注入类目树变量
        // 同时 Laravel 还支持通配符，例如 products.* 即代表当渲染 products 目录下的模板时都执行这个 ViewComposer
        \View::composer(['products.index', 'products.show'], CategoryTreeComposer::class);

        if(!strtoupper(substr(PHP_OS,0,3) == 'WIN') && class_exists('\Horizon')) {
            \Horizon::auth(function ($request) {
                if(Admin::user() && Admin::user()->isAdministrator()){
                    return true;
                }
                throw new InvalidRequestException('老哥，这个就别看了吧');
                return false;
            });
        }

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // 往服务容器中注入一个名为 alipay 的单例对象，用于普通商品支付回调
        $this->app->singleton('alipay', function () {
            $config = config('pay.alipay');
            //$config['notify_url'] = route($config['notify_url_route_name']);
            $config['notify_url'] =  ngrok_url('payment.alipay.notify');
            $config['return_url'] = route($config['return_url_route_name']);
            // 判断当前项目运行环境是否为线上环境
            if (app()->environment() !== 'production') {
                $config['mode']         = 'dev';
                $config['log']['level'] = Logger::DEBUG;
            } else {
                $config['log']['level'] = Logger::WARNING;
            }
            // 调用 Yansongda\Pay 来创建一个支付宝支付对象
            return Pay::alipay($config);
        });

        // 支付宝分期付款的单例,主要是回调地址不同
        $this->app->singleton('alipay_installment', function () {
            $config = config('pay.alipay');
            //$config['notify_url'] = route($config['notify_url_route_name']);
            $config['notify_url'] =  ngrok_url('installments.alipay.notify');
            $config['return_url'] = route('installments.alipay.return');
            // 判断当前项目运行环境是否为线上环境
            if (app()->environment() !== 'production') {
                $config['mode']         = 'dev';
                $config['log']['level'] = Logger::DEBUG;
            } else {
                $config['log']['level'] = Logger::WARNING;
            }
            // 调用 Yansongda\Pay 来创建一个支付宝支付对象
            return Pay::alipay($config);
        });

        //普通商品微信支付回调
        $this->app->singleton('wechat_pay', function () {
            $config = config('pay.wechat');
            $config['notify_url'] = ngrok_url('payment.wechat.notify');
            //$config['notify_url'] = route($config['notify_url_route_name']);
            if (app()->environment() !== 'production') {
                $config['log']['level'] = Logger::DEBUG;
            } else {
                $config['log']['level'] = Logger::WARNING;
            }
            // 调用 Yansongda\Pay 来创建一个微信支付对象
            return Pay::wechat($config);
        });

        //微信分期付款的单例，主要是回调不同
        $this->app->singleton('wechat_pay_installment', function () {
            $config = config('pay.wechat');
            $config['notify_url'] = ngrok_url('installments.wechat.notify');
            //$config['notify_url'] = route($config['notify_url_route_name']);
            if (app()->environment() !== 'production') {
                $config['log']['level'] = Logger::DEBUG;
            } else {
                $config['log']['level'] = Logger::WARNING;
            }
            // 调用 Yansongda\Pay 来创建一个微信支付对象
            return Pay::wechat($config);
        });

        // 注册一个名为 es 的单例
        $this->app->singleton('es', function () {
            // 从配置文件读取 Elasticsearch 服务器列表
            $builder = ClientBuilder::create()->setHosts(config('database.elasticsearch.hosts'));
            // 如果是开发环境
            if (app()->environment() === 'local') {
                // 配置日志，Elasticsearch 的请求和返回数据将打印到日志文件中，方便我们调试
                $builder->setLogger(app('log')->getMonolog());
            }

            return $builder->build();
        });


    }
}
