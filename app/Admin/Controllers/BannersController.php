<?php

namespace App\Admin\Controllers;

use App\Models\Banner;

use App\Models\Product;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use function foo\func;
use Illuminate\Support\Facades\DB;
class BannersController extends Controller
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

            $content->header('轮播图列表');

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

            $content->header('修改轮播图');

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

            $content->header('新增轮播图');

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
        return Admin::grid(Banner::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->url('轮播图地址')->display(function ($val){
                return '<img style="width:100px;height:70px;" src = "'.$val.'" />';
            });
            $grid->place('轮播图位置')->display(function ($val){
                switch ($val) {
                    case 'mini-index':
                        return '小程序首页';
                    case 'pc-index':
                        return 'PC端首页';
                    default:
                        return $val;
                }
            });
            $grid->type('轮播图类型')->display(function ($val){
                return $val == 'A' ? '商品' : '外部链接';
            });
            $grid->product_id('关联商品')->display(function ($val){
                return $val == 0 ? '/' : Product::find($val)->title;
            });
            $grid->link('轮播图链接');
            $grid->title('轮播图标题');
            $grid->isshow('是否显示')->display(function ($val){
                return $val == 0 ? '不显示' : '显示';
            });
            $grid->sort('排序')->sortable();

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


        return Admin::form(Banner::class, function (Form $form){

            $options_data = Product::select(DB::raw('id, title as text'))->get();
            $options = [];
            foreach ($options_data as $v)
            {
                $options[$v['id']] = $v['text'];
            }

            $form->display('id', 'ID');
            $form->file('url', '图片');
            $form->radio('place', '轮播图位置')
                ->options(['mini-index'=>'小程序首页轮播', 'pc-index'=>'PC首页轮播'])
                ->default('mini-index');
            $form->radio('type', '轮播图类型')
                ->options(['A'=>'商品轮播', 'B'=>'外部链接'])
                ->default('A');
            $form->select('product_id', '跳转到的商品页面')->options($options);
            $form->text('link', '如果是外部链接，请在此输入链接，否则不用填写');
            $form->text('title','轮播图标题');
            $form->text('sort','轮播图排序')->default(0)->placeholder('数值越大越靠前');
            $form->radio('isshow','是否显示')->options(['0'=>'不显示','1'=>'显示'])->default('0');
            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }
}
