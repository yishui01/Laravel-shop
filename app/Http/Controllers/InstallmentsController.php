<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use Illuminate\Http\Request;
use App\Exceptions\InvalidRequestException;
use Carbon\Carbon;
use App\Events\OrderPaid;
use Endroid\QrCode\QrCode;
use App\Models\Order;
use App\Models\InstallmentItem;

class InstallmentsController extends Controller
{
    //分期付款列表
    public function index(Request $request)
    {
        $installments = Installment::query()
            ->where('user_id', $request->user()->id)
            ->paginate(10);
        return view('installments.index', compact('installments'));
    }

    //分期付款详情
    public function show(Installment $installment)
    {
        // 取出当前分期付款的所有的还款计划，并按还款顺序排序
        $items = $installment->items()->orderBy('sequence')->get();
        return view('installments.show', [
            'installment' => $installment,
            'items'       => $items,
            // 下一个未完成还款的还款计划
            'nextItem'    => $items->where('paid_at', null)->first(),
        ]);
    }

    //分期付款拉起支付宝支付
    public function payByAlipay(Installment $installment)
    {
        if ($installment->order->closed) {
            throw new InvalidRequestException('对应的商品订单已被关闭');
        }
        if ($installment->status === Installment::STATUS_FINISHED) {
            throw new InvalidRequestException('该分期订单已结清');
        }
        // 获取当前分期付款最近的一个未支付的还款计划
        if (!$nextItem = $installment->items()->whereNull('paid_at')->orderBy('sequence')->first()) {
            // 如果没有未支付的还款，原则上不可能，因为如果分期已结清则在上一个判断就退出了
            throw new InvalidRequestException('该分期订单已结清');
        }
        // 调用支付宝的网页支付，这里实例化的是分期回调
        return app('alipay_installment')->web([
            // 支付订单号使用分期流水号+还款计划编号
            'out_trade_no' => $installment->no.'_'.$nextItem->sequence,
            'total_amount' => $nextItem->total,
            'subject'      => '支付 Laravel Shop 的分期订单：'.$installment->no,
        ]);
    }


    //分期付款拉起微信支付
    public function payByWechat(Installment $installment)
    {
        if ($installment->order->closed) {
            throw new InvalidRequestException('对应的商品订单已被关闭');
        }
        if ($installment->status === Installment::STATUS_FINISHED) {
            throw new InvalidRequestException('该分期订单已结清');
        }
        if (!$nextItem = $installment->items()->whereNull('paid_at')->orderBy('sequence')->first()) {
            throw new InvalidRequestException('该分期订单已结清');
        }

        $wechatOrder = app('wechat_pay_installment')->scan([
            'out_trade_no' => $installment->no.'_'.$nextItem->sequence,
            'total_fee'    => $nextItem->total * 100,
            'body'         => '支付 Laravel Shop 的分期订单：'.$installment->no,
        ]);
        // 把要转换的字符串作为 QrCode 的构造函数参数
        $qrCode = new QrCode($wechatOrder->code_url);

        // 将生成的二维码图片数据以字符串形式输出，并带上相应的响应类型
        return response($qrCode->writeString(), 200, ['Content-Type' => $qrCode->getContentType()]);
    }

    // 分期付款成功支付宝前端回调
    public function alipayReturn()
    {
        try {
            app('alipay_installment')->verify();
        } catch (\Exception $e) {
            return view('pages.error', ['msg' => '数据不正确']);
        }

        return view('pages.success', ['msg' => '付款成功']);
    }

    // 分期付款成功支付宝后端回调
    public function alipayNotify()
    {
        // 校验支付宝回调参数是否正确
        $data = app('alipay_installment')->verify();
        if ($this->paid($data->out_trade_no, 'alipay', $data->trade_no)) {
            return app('alipay_installment')->success();
        }

        return 'fail';
    }


    //分期付款成功微信支付服务端回调
    public function wechatNotify()
    {
        $data = app('wechat_pay_installment')->verify();
        if ($this->paid($data->out_trade_no, 'wechat', $data->transaction_id)) {
            return app('wechat_pay_installment')->success();
        }

        return 'fail';
    }

    //付款成功后系统回调
    protected function paid($outTradeNo, $paymentMethod, $paymentNo)
    {
        list($no, $sequence) = explode('_', $outTradeNo);
        if (!$installment = Installment::where('no', $no)->first()) {
            return false;
        }
        if (!$item = $installment->items()->where('sequence', $sequence)->first()) {
            return false;
        }
        if ($item->paid_at) {
            return true;
        }

        $item->update([
            'paid_at'        => Carbon::now(),
            'payment_method' => $paymentMethod,
            'payment_no'     => $paymentNo,
        ]);

        if ($item->sequence === 0) {
            $installment->update(['status' => Installment::STATUS_REPAYING]);
            $installment->order->update([
                'paid_at'        => Carbon::now(),
                'payment_method' => 'installment',
                'payment_no'     => $no,
            ]);
            event(new OrderPaid($installment->order));
        }
        if ($item->sequence === $installment->count - 1) {
            $installment->update(['status' => Installment::STATUS_FINISHED]);
        }

        return true;
    }

    //微信分期付款退款服务端回调地址
    public function wechatRefundNotify(Request $request)
    {
        // 给微信的失败响应
        $failXml = '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[FAIL]]></return_msg></xml>';
        // 校验微信回调参数
        $data = app('wechat_pay')->verify(null, true);
        // 根据单号拆解出对应的商品退款单号及对应的还款计划序号
        list($no, $sequence) = explode('_', $data['out_refund_no']);

        $item = InstallmentItem::query()
            ->whereHas('installment', function ($query) use ($no) {
                $query->whereHas('order', function ($query) use ($no) {
                    $query->where('refund_no', $no); // 根据订单表的退款流水号找到对应还款计划
                });
            })
            ->where('sequence', $sequence)
            ->first();

        // 没有找到对应的订单，原则上不可能发生，保证代码健壮性
        if (!$item) {
            return $failXml;
        }

        // 如果退款成功
        if ($data['refund_status'] === 'SUCCESS') {
            // 将还款计划退款状态改成退款成功
            $item->update([
                'refund_status' => InstallmentItem::REFUND_STATUS_SUCCESS,
            ]);
            //验证当前所有还款计划是否全部退款完成
            $item->installment->refreshRefundStatus();
        } else {
            // 否则将对应还款计划的退款状态改为退款失败
            $item->update([
                'refund_status' => InstallmentItem::REFUND_STATUS_FAILED,
            ]);
        }

        return app('wechat_pay')->success();
    }


}
