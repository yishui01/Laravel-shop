<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedProductsData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //填充商品模块的四个表的数据
        //分类表
        $categories_data = [
            'name'      => '手机',
            'parent_id' => 0,
            'score'     => 0, //排序字段
            'isshow'    => 'A' //A为显示B为不显示
        ];
        $category = \App\Models\Category::create($categories_data);

        //商品表
        $products_data = [
            'title'       => 'iphone6s',
            'description' => '新品上市',
            'image'       => 'https://img13.360buyimg.com/n1/s450x450_jfs/t7369/88/1302655817/65372/de8c58bf/599bf84dN8b816781.jpg',
            'on_sale'     => 1, //是否上架
            'rating'      => 5, //评分
            'sold_count'  => 0, //销量
            'review_count'=> 0, //评论数
            'price'       => 0, //最低SKU价格
            'category_id' => $category->id
        ];
        $product = \App\Models\Product::create($products_data);

        //商品-属性表
        $product_attributes_data = [
            [
                'product_id'     => $product->id,
                'name'           => '颜色',
                'hasmany'        => '1', //属性是否可选
                'val'            => '',
                'test_val'       => [ //属性可选值，该表中无该字段，此处用于数据填充
                    '深空灰', '玫瑰金', '女神紫', '土豪金'
                ]
            ],
            [
                'product_id'     => $product->id,
                'name'           => '大小',
                'hasmany'        => '1',
                'val'            => '',
                'test_val'       => [
                    '4.7寸', '5.5寸'
                ]
            ],
            [
                'product_id'     => $product->id,
                'name'           => '生产日期',
                'hasmany'        => '0',
                'val'            => '',
                'test_val'       => ['2018-08-08']
            ],
        ];
        foreach ($product_attributes_data as $k=>$v) {
            $pro_attr = \App\Models\ProductAttribute::create($v);
            //商品属性值表
            foreach ($v['test_val'] as $v1) {
                \App\Models\Attribute::create([
                    'product_id' => $v['product_id'],
                    'attr_id'    => $pro_attr->id,
                    'attr_val'   => $v1
                ]);
            }
        }

        //随机添加SKU,把所有可选属性找出来就行
        $hasManyAttr = \App\Models\ProductAttribute::where([['product_id', $product->id], ['hasmany', 1]])
            ->with('attribute')->get();
        foreach ($hasManyAttr as $attr) {
            $res_arr = $this->__dfs($attr->attribute);
        }

        foreach ($res_arr as $k=>$v) {
            \App\Models\ProductSku::create([
                'title' =>$v['attr_val'],
                'description' =>'',
                'price' =>random_int(3000,3999),
                'stock' =>random_int(100,500),
                'product_id' =>$product->id,
                'attributes' =>$v['id'],
            ]);
        }

    }

    //把传进来的$arr和$res_arr做一个全排列
    private function __dfs($arr)
    {
        static $res_arr = [];
        if (empty($res_arr)) {
            foreach ($arr as $k=>$v) {
                $res_arr[] = ['id'=>$v->id, 'attr_val'=>$v->attr_val];
            }
        } else {
            $tmp = $res_arr;
            $res_arr = [];
            foreach ($tmp as $k=>$v) {
                foreach ($arr as $k1=>$v1) {
                    $res_arr[] = ['id'=>$v['id'].','.$v1->id, 'attr_val'=>$v['attr_val'].','.$v1->attr_val];
                }
            }
        }
        return $res_arr;
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::table('categories')->truncate();
        \DB::table('products')->truncate();
        \DB::table('product_skus')->truncate();
        \DB::table('product_attributes')->truncate();
        \DB::table('attributes')->truncate();
    }
}
