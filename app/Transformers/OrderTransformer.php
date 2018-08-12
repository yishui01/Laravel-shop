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
            'updated_at'      => $order->updated_at,
            'paid_at'         => $order->paid_at,
            'refund_no'       => $order->refund_no,
            'refund_status'   => $order->refund_status,
            'reviewed'        => $order->reviewed,
            'ship_status'     => $order->ship_status,
            'total_amount'    => $order->total_amount,
            'items'           => $order->items
        ];
    }
}