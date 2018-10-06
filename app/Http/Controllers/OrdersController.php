<?php

namespace App\Http\Controllers;

use App\Events\OrderReviewd;
use App\Http\Requests\ApplyRefundRequest;
use App\Http\Requests\CrowdFundingOrderRequest;
use App\Http\Requests\OrderRequest;
use App\Http\Requests\Request;
use App\Http\Requests\SeckillOrderRequest;
use App\Http\Requests\SendReviewRequest;
use App\Models\ProductSku;
use App\Models\UserAddress;
use App\Models\Order;
use App\Services\CartService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\SystemException;
use App\Jobs\CloseOrder;
use App\Services\OrderService;
use App\Exceptions\InvalidRequestException;
use App\Exceptions\CouponCodeUnavailableException;
use App\Models\CouponCode;
class OrdersController extends Controller
{
    //普通商品下单
    public function store(OrderRequest $request, OrderService $orderService)
    {

        $user    = $request->user();
        $address = UserAddress::find($request->input('address_id'));
        $coupon = null;

        // 如果用户提交了优惠码
        if ($code = $request->input('coupon_code')) {
            $coupon = CouponCode::where([
                ['code','=', $code],
                ['enabled','=', 1],
            ])->first();
            if (!$coupon) {
                throw new CouponCodeUnavailableException('优惠券不存在');
            }
        }

        return $orderService->store($user, $address, $request->input('remark'), $request->input('items'), $coupon);
    }

    //众筹商品下单
    public function crowdfunding(CrowdFundingOrderRequest $request, OrderService $orderService)
    {
        $user    = $request->user();
        $sku     = ProductSku::find($request->input('sku_id'));
        $address = UserAddress::find($request->input('address_id'));
        $amount  = $request->input('amount'); //购买数量

        return $orderService->crowdfunding($user, $address, $sku, $amount);
    }

    //秒杀商品下单
    public function seckill(SeckillOrderRequest $request, OrderService $orderService)
    {
        $user    = $request->user();
        $address = UserAddress::find($request->input('address_id'));
        $sku     = ProductSku::find($request->input('sku_id'));

        return $orderService->seckill($user, $request->input('address'), $sku);
    }

    //订单列表页
    public function index()
    {
        $orders = Auth::user()->orders()->with(['items.productSku', 'items.product']) ->orderBy('created_at', 'desc')->paginate();
        return view('orders.index', ['orders' => $orders]);
    }

    //订单详情页面
    public function show(Order $order)
    {
        $this->authorize('own', $order);
        return view('orders.show', ['order'=>$order->load(['items.productSku', 'items.product'])]);
    }

    //用户确认收货
    public function received(Order $order, Request $request)
    {

        // 校验权限
        $this->authorize('own', $order);
        // 判断订单的发货状态是否为已发货
        if ($order->ship_status !== Order::SHIP_STATUS_DELIVERED) {
            throw new InvalidRequestException('该订单还未发货');
        }
        $order->ship_status = Order::SHIP_STATUS_RECEIVED;
        $order->save();
        // 返回订单信息
        return $order;
    }

    //评价表单
    public function review(Order $order)
    {
        //校验权限
        $this->authorize('own', $order);
        //判断是否已经支付
         if (!$order->paid_at) {
             throw new InvalidRequestException('该订单未支付');
         }
         //使用load方法加载关联数据，避免N+1问题
        return view('orders.review', ['order'=>$order->load(['items.productSku', 'items.product'])]);
    }

    //发表评价
    public function sendReview(Order $order, SendReviewRequest $request)
    {
        //校验权限
        $this->authorize('own', $order);
        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未支付，不可评价');
        }
        if ($order->reviewed) {
            throw new InvalidRequestException('该订单已评价，不可重复评价');
        }
        $reviews = $request->input('reviews');
        //开启事务
        \DB::transaction(function () use ($reviews, $order) {
            //遍历用户提交的评论数据
            foreach ($reviews as $review) {
                $orderItem = $order->items()->find($review['id']);
                //保存评分和评价
                $orderItem->update([
                    'rating' => $review['rating'],
                    'review' => $review['review'],
                    'reviewed_at'=> Carbon::now(),
                ]);
            }
            $order->update(['reviewed'=>true]);
            event(new OrderReviewd($order));
        });
        return redirect()->back();
    }

    //用户申请退款接口
    public function applyRefund(Order $order, ApplyRefundRequest $request)
    {
        //校验订单是否属于当前用户
        $this->authorize('own', $order);
        //判断订单是否已经付款
        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未支付，不可退款');
        }
        // 判断订单退款状态是否是未退款
        if ($order->refund_status !== Order::REFUND_STATUS_PENDING) {
            throw new InvalidRequestException('该订单已经申请过退款，请勿重复申请');
        }
        // 众筹订单不允许申请退款
        if ($order->type === Order::TYPE_CROWDFUNDING) {
            throw new InvalidRequestException('众筹订单不支持退款');
        }
        //将用户输入的是退款的理由放到订单的extra字段中
        $extra = $order->extra ? : [];
        $extra['refund_reason'] = $request->input('reason');
        //将订单退款状态理由放到订单的extra字段中
        $order->update([
            'refund_status'=>Order::REFUND_STATUS_APPLIED, //已申请退款
            'extra'=>$extra
        ]);

        return $order;
    }


}