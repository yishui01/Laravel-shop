<?php

namespace App\Transformers;

use App\Models\Order;
use League\Fractal\TransformerAbstract;

class OrderTransformer extends TransformerAbstract
{
    public function transform(Order $order)
    {
        return [
            'id'              => $order->id,
            'remark'          => $order->remark,
            'no'              => $order->no,
            'address'         => $order->address,
            'updated_at'      => $order->updated_at->toDateTimeString(),
            'paid_at'         => $order->paid_at,
            'refund_no'       => $order->refund_no,
            'refund_status'   => $order->refund_status,
            'reviewed'        => $order->reviewed,
            'ship_status'     => $order->ship_status,
            'ship_data'       => $order->ship_data,
            'total_amount'    => $order->total_amount,
            'items'           => $order->items,
            'closed'           => $order->closed,
            'status'          => $order->status  //这个字段是额外加的用于前端筛选，数据库中并没有这个字段
        ];
    }
}