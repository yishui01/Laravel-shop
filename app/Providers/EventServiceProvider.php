<?php

namespace App\Providers;

use App\Events\OrderReviewd;
use App\Listeners\UpdateCrowdfundingProductProgress;
use App\Listeners\UpdateProductRating;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Auth\Events\Registered;
use App\Listeners\RegisteredListener;
use App\Events\OrderPaid;
use App\Listeners\UpdateProductSoldCount;
use App\Listeners\SendOrderPaidMail;
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\Event' => [
            'App\Listeners\EventListener',
        ],
        Registered::class => [
            RegisteredListener::class, //用户邮箱注册时发送注册邮件
        ],
        OrderPaid::class => [
            UpdateProductSoldCount::class, //更新商品销量
            SendOrderPaidMail::class, //发邮件通知支付成功
            UpdateCrowdfundingProductProgress::class, //更新众筹信息
        ],
        OrderReviewd::class=>[
            UpdateProductRating::class //更新商品评价分数
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
