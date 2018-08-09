<?php

namespace App\Admin\Controllers;

use App\Models\Product;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use App\Models\Category;

class ProductsController extends Controller
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

            $content->header('商品列表');
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

            $content->header('修改商品');
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

            $content->header('创建商品');
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
        return Admin::grid(Product::class, function (Grid $grid) {
            $grid->model()->orderBy('id', 'desc');
            $grid->id('ID')->sortable();

            $grid->title('商品名称');
            $grid->on_sale('已上架')->display(function ($value) {
                return $value ? '是' : '否';
            });
            $grid->price('价格');
            $grid->category_id('商品分类')->display(function ($val){
                if ($val) {
                    if ($cate = Category::find($val)) {
                        return $cate->name;
                    } else {
                        return '该商品分类已被删除';
                    }
                } else {
                    return '顶级分类';
                }
            });
            $grid->rating('评分');
            $grid->sold_count('销量');
            $grid->review_count('评论数');

            $grid->tools(function ($tools) {
                // 禁用批量删除按钮
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });
            $grid->created_at();
            $grid->updated_at();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($id = 0)
    {
        $category = new Category();
        $options_data = $category->getCateList(0,0);
        $options = [];
        foreach ($options_data as $k => $v) {
            $options[$v['id']] = $v['text'];
        }
        $category_id = (int)($id ? Product::find($id)->category_id : 0);
        return Admin::form(Product::class, function (Form $form)  use ($options, $category_id){

            // 创建一个输入框，第一个参数 title 是模型的字段名，第二个参数是该字段描述
            $form->text('title', '商品名称')->rules('required');
            $form->select('category_id', '商品分类')->options($options)->default($category_id)->rules('required');

            // 创建一个选择图片的框
            $form->image('image', '封面图片')->rules('required|image');

            // 创建一个富文本编辑器
            $form->editor('description', '商品描述')->rules('required');

            // 创建一组单选框
            $form->radio('on_sale', '上架')->options(['1' => '是', '0'=> '否'])->default('0');

            $king = $form;
            // 直接添加一对多的关联模型
            $form->hasMany('pro_attr', '商品属性', function (Form\NestedForm $form) use ($king) {
                $form->text('name', '属性名称')->placeholder('请输入该商品具有的属性名称，例如:颜色')->rules('required');
                $form->radio('hasmany', '属性是否可选')->options(['1' => '可选', '0'=> '唯一'])->default('1')->rules('required');
                $form->text('val', '属性值')->placeholder('当属性为可选时（多个属性值），用逗号分隔')->rules('required');
            });
            // 定义事件回调，当模型即将保存时会触发这个回调,找出最低的价格，存到商品表
            $form->saving(function (Form $form) {
                $form->model()->price = 0;
                //$form->model()->price = collect($form->input('skus'))->where(Form::REMOVE_FLAG_NAME, 0)->min('price');
            });

        });
    }


}
