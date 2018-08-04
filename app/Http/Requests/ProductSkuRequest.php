<?php

namespace App\Http\Requests;

use App\Exceptions\InvalidRequestException;
use App\Models\Attribute;
use App\Models\ProductSku;
use Illuminate\Support\Facades\DB;

class ProductSkuRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'price'=>['required','numeric'],
            'stock'=>['required','numeric'],
            'product_id'=>['required','integer'],
            'attributes'=>['required', function($attribute, $value, $fail) {
                //验证商品是否存在
                $product = ProductSku::find($this->input('product_id'));
                if (!$product) {
                    throw new InvalidRequestException('商品不存在', 400);
                }

                \DB::transaction(function (){
                    $sku_arr = json_decode($this->input('attributes'), true);
                    //把本次输入的SKU属性值添加到属性值表中，已存在的值不用添加
                    $id_arr = [];
                    $val_arr = [];
                    foreach ($sku_arr as $k=>$v) {
                        $where = [
                            ['attr_id', '=', $v['id']],
                            ['product_id', '=', $this->input('product_id')],
                            ['attr_val', '=', $v['value']]
                        ];
                        $attr = Attribute::where($where)->first();
                        if (!$attr) {

                            $obj = Attribute::create([
                                'attr_id'=>$v['id'],
                                'attr_val'=>$v['value'],
                                'product_id'=>$this->input('product_id')
                            ]);
                            $id_arr[]=$obj->id;
                            $val_arr[] = $obj->attr_val;
                        } else {
                            $id_arr[] = $attr->id;
                            $val_arr[] = $attr->attr_val;
                        }
                    }
                    //验证本次添加的SKU属性组合是否重复
                    $sku_id = request()->input('id');
                    if($sku_id) {
                        /*****如果是更新的话先删除本字段的sku值，因为更新的时候要重新刷新该字段的*******/
                        ProductSku::where('id', $sku_id)->update([
                            'attributes'=>''
                        ]);
                    }
                    //取出现有商品的SKU列表
                    $now_sku = ProductSku::select(DB::raw('attributes'))->where('product_id', $this->input('product_id'))->get()->toArray();
                    $flag = true;

                    if (!empty($now_sku)) {
                        foreach ($now_sku as $k=>$v) {
                            $tmp = explode(',', $v['attributes']);
                            if (!array_diff($tmp, $id_arr)) {
                                $flag = false;
                                break;
                            }
                        }
                    }
                    if(!$flag){
                        throw new InvalidRequestException('该商品SKU已经存在！');
                    }
                    if($sku_id) {
                        //更新
                        $sku_obj = ProductSku::find($sku_id);
                        $sku_obj->fill(request()->all());
                        $sku_obj->attributes = implode(',', $id_arr);
                        $sku_obj->title = implode(',',$val_arr); //冗余字段
                        $sku_obj->save();
                    } else {
                        //创建
                        $sku_obj = new ProductSku();
                        $sku_obj->fill(request()->all());
                        $sku_obj->attributes = implode(',', $id_arr);
                        $sku_obj->title = implode(',',$val_arr); //冗余字段
                        $sku_obj->save();
                    }

                });

            }]
        ];
    }

    public function attributes()
    {
        return [
            'price'=>'价格',
            'stock'=>'库存',
            'product_id'=>'商品'
        ];
    }

}
