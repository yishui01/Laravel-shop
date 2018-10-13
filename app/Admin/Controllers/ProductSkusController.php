<?php

namespace App\Admin\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Exceptions\SystemException;
use App\Http\Requests\ProductSkuRequest;
use App\Http\Requests\Request;
use App\Models\Attribute;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductSku;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class ProductSkusController extends Controller
{
    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('库存管理');
            $content->description('description');
            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        $sku = ProductSku::find($id);
        if (!$sku) {
            throw new InvalidRequestException('未找到该商品');
        }

        $attr_id = explode(',', $sku->attributes);
        $attributes = []; //SKU所具有的属性

        if (!empty($attr_id)) {
            $attributes = DB::table('attributes as a')
                ->select('a.attr_val','b.id as pro_attr_id','b.name')
                ->leftJoin('product_attributes as b', 'a.attr_id','=', 'b.id')
                ->whereIn('a.id',$attr_id)
                ->get();
        }

        return Admin::content(function (Content $content) use ($sku, $attributes) {
            $content->header('修改库存');
            $products = $this->getProduct();
            $content->body(view('admin.product_sku.create_and_edit', compact('products', 'sku', 'attributes')));

        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {
            $content->header('添加库存');
            $products = $this->getProduct();
            $sku = new ProductSku();
            $attributes = [];
            $content->body(view('admin.product_sku.create_and_edit', compact('products', 'sku', 'attributes')));

        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(ProductSku::class, function (Grid $grid) {
            Admin::script("$('a[href=\"/admin/skus/create\"]').attr('no-pjax', '');");
            Admin::script("$('a[href$=\"/edit\"]').attr('no-pjax', '');");
            $grid->model()->orderBy('id', 'desc');
            $grid->id('ID')->sortable();

            // 第二列显示title字段，由于title字段名和Grid对象的title方法冲突，所以用Grid的column()方法代替
            //$grid->column('title');
            $grid->product_id('所属商品')->display(function($product_id) {
                return Product::find($product_id) ? Product::find($product_id)->title : '';
            });

            $grid->title('属性');

            $grid->price('价格')->sortable();

            $grid->stock('当前库存')->sortable();

            $grid->description('描述');

            $grid->updated_at('修改时间')->sortable();

            $grid->filter(function ($filter){
                $filter->like('product.title', '所属商品');
                $filter->like('title', '商品属性');
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        //找出这个商品所具有的全部属性
        return Admin::form(ProductSku::class, function (Form $form) {


            $form->display('id', 'ID');
            $form->select('product_id', '选择商品')->options('api')->rules('required');
            $form->text('description', '描述');
            $form->text('price', '价格');
            $form->number('stock', '库存');
            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }

    //获取商品列表API
    public function getProduct()
    {
        return Product::select(DB::raw('id, title as text'))->get();
    }

    //获取商品属性API
    public function getAttributes($id)
    {
        return ProductAttribute::where([
            ['hasmany', '=', '1'],
            ['product_id', '=', $id]
        ])->get();
    }

    public function store(ProductSkuRequest $request)
    {
        //创建
        $this->skuSave();
        return [];
    }

    public function update(ProductSkuRequest $request)
    {
        //更新
        $this->skuSave();
        return [];
    }

    public function destroy($id)
    {
        if ($this->form()->destroy($id)) {
            return response()->json([
                'status'  => true,
                'message' => trans('admin.delete_succeeded'),
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => trans('admin.delete_failed'),
            ]);
        }
    }

    public function skuSave()
    {

        DB::transaction(function (){
            if ( !($sku_obj = ProductSku::find(request()->input('id')))) {
                $sku_obj = new ProductSku();
            }
            $sku_obj->fill(request()->all());
            $attr_arr = json_decode(request()->input('attributes'), true);
            $id_arr = [];
            $val_arr = [];
            if (!empty($attr_arr)) {
                //先向属性值表中添加属性，在把生成的ID拼成字符串添加到表中
                foreach ($attr_arr as $v) {
                    //看属性是否存在，有的话就不用添加了
                    $obj = Attribute::where([
                        ['product_id','=', request()->input('product_id')],
                        ['attr_id', '=', $v['id']],
                        ['attr_val', '=', $v['value']]
                    ])->first();
                    if (!$obj) {
                        $obj = Attribute::create([
                            'product_id'=>request()->input('product_id'),
                            'attr_id'=>$v['id'],
                            'attr_val'=>$v['value']
                        ]);
                    }

                    $id_arr[] = $obj->id;
                    $val_arr[] = $obj->attr_val;

                }
            }
            $sku_obj->description  = $sku_obj->description ?? '';
            $sku_obj->attributes = implode(',',$id_arr);
            $sku_obj->title = implode(',',$val_arr); //冗余字段
            $sku_obj->save();
        });
    }

}
