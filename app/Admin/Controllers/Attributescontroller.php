<?php

namespace App\Admin\Controllers;

use App\Jobs\SyncOneProductToES;
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
use function foo\func;

class AttributesController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('header');
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
        return Admin::content(function (Content $content) use ($id) {

            $content->header('header');
            $content->description('description');

            $content->body($this->form()->edit($id));
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

            $content->header('header');
            $content->description('description');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Attribute::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->product_id('所属商品')->display(function ($val){
                $name = Product::where('id', $val)->value('title');
                return $name ?? '该商品已被删除';
            });
            $grid->attr_id('属性名称')->display(function ($val){
                $name = ProductAttribute::where('id', $val)->value('name');
                return $name ?? '该属性已被删除';
            });
            $grid->attr_val('属性值');
            $grid->filter(function($filter){
                $filter->where(function ($query) {
                    $query->whereHas('product', function ($query) {
                        $query->where('title', 'like', "%{$this->input}%");
                    });
                }, '商品名称');

                $filter->like('attr_val', '属性值名称');
            });
            $grid->disableCreateButton();
            $grid->created_at();
            $grid->updated_at();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Attribute::class, function (Form $form) {

            $form->text('attr_val', '属性值名称');
        });
    }

    public function destroy($id)
    {
        //更新ES数据库
        $product = Attribute::find($id)->product;
        if($product){
            dispatch(new SyncOneProductToES($product));
        }
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
}
