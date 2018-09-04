<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\CouponCodeRequest;
use App\Models\CouponCode;
use App\Transformers\CouponCodeTransformer;
use Illuminate\Http\Request;
use App\Models\Order;
class CouponCodesController extends Controller
{
    //优惠券列表接口
    public function index()
    {
        $data = CouponCode::where([
            ['enabled','=', 1], //是否启用
        ])->where(function ($query){
            //not_before的字段值开始时间
            $query->whereNull('not_before')->orWhere('not_before','<=', date('Y-m-d H:i:s', time()));
        }) ->where(function ($query){
            //not_after的字段值为结束时间
            $query->whereNull('not_after')->orWhere('not_after','>=', date('Y-m-d H:i:s', time()));
        })->get();

        foreach ($data as $k=>$v) {
            //过滤掉库存不足的优惠券
            if ($v['used'] >= $v['total'])unset($data[$k]);
        }
        return $this->response->collection($data, new CouponCodeTransformer())
            ->setStatusCode($this->success_code);
    }

    //用户领取优惠券接口
    public function receive(CouponCodeRequest $request)
    {
        $couponCode = CouponCode::where([
            ['code','=', $request->code],
            ['enabled','=', 1],
        ])->firstOrFail();

        if (($couponCode->total - $couponCode->used)  <= 0) {
            return $this->response->error('该优惠券已经发放完毕啦',$this->forbidden_code);
        }

        //判断当前用户是否已经使用过该优惠券，如果没有使用过就可以领取
        //领取结果并不会保存在数据库，只会返回201，小程序收到201之后缓存优惠券在本地，
        //下单时带上优惠券，下单接口会判断优惠券是否有效
        $user = $this->user(); //当前已登陆的用户
        $builder = Order::query()->where('user_id', $user->id);
        $is_used = $builder->where(function ($query){
            $query->where(function ($query){
                $query->whereNull('paid_at')
                    ->where('closed', false);
            })->orWhere(function ($query){
                $query->whereNotNull('paid_at')
                    ->where('refund_status', Order::REFUND_STATUS_PENDING);
            });
        })->where('coupon_code_id', $couponCode->id)->exists();

        if ($is_used) {
            //如果存在，那就是已经使用过优惠券了
            return $this->response->error('您已经使用过这张优惠券了',$this->forbidden_code);
        }
        return $this->response->item($couponCode, new CouponCodeTransformer())
            ->setStatusCode($this->success_code);
    }
}
