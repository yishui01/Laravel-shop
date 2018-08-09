<?php

namespace App\Listeners;

use App\Events\OrderReviewd;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\OrderItem;

class UpdateProductRating
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  OrderReviewd  $event
     * @return void
     */
    public function handle(OrderReviewd $event)
    {
        // 通过 with 方法提前加载数据，避免 N + 1 性能问题
        $items = $event->getOrder()->items()->with(['product'])->get();
        foreach ($items as $item) {
            //获取平均评分
            $rating_result = OrderItem::query()
                ->where('product_id', $item->product_id)
                ->whereHas('order', function ($query) {
                    $query->whereNotNull('paid_at');
                })
                ->first([
                    \DB::raw('avg(rating) as rating')
                ]);
            //获取订单总评价数量
            $review_result = OrderItem::query()
                ->where('product_id', $item->product_id)
                ->whereHas('order', function ($query) {
                    $query->whereNotNull('reviewed_at');
                })
                ->first([
                    \DB::raw('count(*) as review_count'),
                ]);

            // 更新商品的评分和评价数
            $item->product->update([
                'rating'       => $rating_result->rating,
                'review_count' => $review_result->review_count,
            ]);
        }
    }
}
