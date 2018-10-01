<?php

namespace App\Admin\Controllers;

use App\Models\CrowdfundingProduct;
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

class CrowdfundingProductsController extends Controller
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

            $content->header('众筹商品列表');

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

            $content->header('修改众筹商品');

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

            $content->header('创建众筹商品');
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

            $grid->model()->where('type',Product::TYPE_CROWDFUNDING)->orderBy('id', 'desc');
            $grid->id('ID')->sortable();
            $grid->title('商品名称');
            $grid->on_sale('已上架')->display(function ($value) {
                return $value ? '是' : '否';
            });
            $grid->price('价格');
            // 展示众筹相关字段
            $grid->column('crowdfunding.target_amount', '目标金额');
            $grid->column('crowdfunding.end_at', '结束时间');
            $grid->column('crowdfunding.total_amount', '目前金额');
            $grid->column('crowdfunding.status', ' 状态')->display(function ($value) {
                return CrowdfundingProduct::$statusMap[$value] ?? '未知状态';
            });
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
        $options_data = Category::all();
        $options = [];
        foreach ($options_data as $k => $v) {
            $options[$v['id']] = $v->full_name;
        }
        $category_id = (int)($id ? Product::find($id)->category_id : 0);
        return Admin::form(Product::class, function (Form $form)  use ($options, $category_id){
            // 创建一个输入框，第一个参数 title 是模型的字段名，第二个参数是该字段描述
            $form->hidden('type')->value(Product::TYPE_CROWDFUNDING);
            $form->text('title', '商品名称')->rules('required');
            $form->select('category_id', '商品分类')
                ->options($options)->default($category_id)->rules('required');

            // 创建一个选择图片的框
            $form->image('image', '封面图片')->rules('required|image');

            // 创建一个富文本编辑器
            $form->editor('description', '商品描述')->rules('required');

            // 创建一组单选框
            $form->radio('on_sale', '上架')->options(['1' => '是', '0'=> '否'])->default('0');
            // 添加众筹相关字段
            $form->text('crowdfunding.target_amount', '众筹目标金额')->rules('required|numeric|min:0.01');
            $form->datetime('crowdfunding.end_at', '众筹结束时间')->rules('required|date');

            // 直接添加一对多的关联模型
            $form->hasMany('pro_attr', '商品属性', function (Form\NestedForm $form){
                $form->text('name', '属性名称')->placeholder('请输入该商品具有的属性名称，例如:颜色')->rules('required');
                $form->radio('hasmany', '属性是否可选')->help('可选代表用户可以选择的属性，比如衣服这个商品的可选属性就是大小、颜色,这些是用户可以选的，唯一的属性比如衣服的生产厂家、生产日期，这样的属性，用户没得选，唯一属性会列在商品介绍中，供用户参考')
                    ->options(['1' => '可选', '0'=> '唯一'])->default('1')->rules('required');
                $form->text('val', '属性值')->placeholder('当属性为唯一时填写该项，可选属性不用填写该项');
            });

        });
    }


}
