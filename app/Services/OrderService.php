<?php

namespace App\Services;

use App\Models\CouponCode;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Order;
use App\Models\ProductSku;
use App\Exceptions\InvalidRequestException;
use App\Jobs\CloseOrder;
use Carbon\Carbon;
use App\Exceptions\CouponCodeUnavailableException;

class OrderService
{
    /*$user、$address 变量改为从参数获取。我们在封装功能的时候有一点一定要注意，
    $request 不可以出现在控制器和中间件以外的地方，根据职责单一原则，获取数据这个任务应该由控制器来完成，
    封装的类只需要专注于业务逻辑的实现。
CartService 的调用方式改为了通过 app() 函数创建，因为这个 store() 方法是我们手动调用的，
    无法通过 Laravel 容器的自动解析来注入。在我们代码里调用封装的库时一定 不可以 使用 new 关键字来初始化，
    而是应该通过 Laravel 的容器来初始化，因为在之后的开发过程中 CartService 类的构造函数可能会发生变化，
    比如注入了其他的类，如果我们使用 new 来初始化的话，就需要在每个调用此类的地方进行修改；
    而使用 app() 或者自动解析注入等方式 Laravel 则会自动帮我们处理掉这些依赖。
之前在控制器中可以通过 $this->dispatch() 方法来触发任务类，但在我们的封装的类中并没有这个方法，
    因此关闭订单的任务类改为 dispatch() 辅助函数来触发。*/
    public function store(User $user, UserAddress $address, $remark, $items, CouponCode $coupon = null)
    {

        // 如果传入了优惠券，则先检查是否可用
        if ($coupon) {
            // 但此时我们还没有计算出订单总金额，因此先不传订单总金额，不会校验是否达到最小金额
            $coupon->checkAvailable($user);
        }

        // 开启一个数据库事务
        $order = \DB::transaction(function () use ($user, $address, $remark, $items, $coupon) {
            // 更新此地址的最后使用时间
            $address->update(['last_used_at' => Carbon::now()]);
            // 创建一个订单
            $order   = new Order([
                'address'      => [ // 将地址信息放入订单中
                    'address'       => $address->full_address,
                    'zip'           => $address->zip,
                    'contact_name'  => $address->contact_name,
                    'contact_phone' => $address->contact_phone,
                ],
                'remark'       => $remark,
                'total_amount' => 0,
            ]);

            // 订单关联到当前用户
            $order->user()->associate($user);
            // 写入数据库
            $order->save();

            $totalAmount = 0;
            // 遍历用户提交的 SKU
            foreach ($items as $data) {
                $sku  = ProductSku::find($data['sku_id']);
                // 创建一个 OrderItem 并直接与当前订单关联
                $item = $order->items()->make([
                    'amount' => $data['amount'],
                    'price'  => $sku->price,
                ]);
                $item->product()->associate($sku->product_id);
                $item->productSku()->associate($sku);
                $item->save();
                $totalAmount += $sku->price * $data['amount'];
                if ($sku->decreaseStock($data['amount']) <= 0) {
                    throw new InvalidRequestException('该商品库存不足');
                }
            }
            if ($coupon) {
                // 总金额已经计算出来了，检查是否符合优惠券规则
                $coupon->checkAvailable($user, $totalAmount);
                // 把订单金额修改为优惠后的金额
                $totalAmount = $coupon->getAdjustedPrice($totalAmount);
                // 将订单与优惠券关联
                $order->couponCode()->associate($coupon);
                // 增加优惠券的用量，需判断返回值
                if ($coupon->changeUsed() <= 0) {
                    throw new CouponCodeUnavailableException('该优惠券已被兑完');
                }
            }
            // 更新订单总金额
            $order->update(['total_amount' => $totalAmount]);

            // 将下单的商品从购物车中移除
            $skuIds = collect($items)->pluck('sku_id')->all();
            app(CartService::class)->remove($skuIds);

            return $order;
        });

        // 这里我们直接使用 dispatch 函数
        dispatch(new CloseOrder($order, config('myconfig.order.order_ttl')));

        return $order;
    }
}