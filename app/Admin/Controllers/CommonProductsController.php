<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\SyncOneProductToES;
use App\Models\Attribute;
use App\Models\ProductAttribute;
use App\Models\ProductSku;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\ModelForm;

use App\Models\Product;
use App\Models\Category;

abstract class CommonProductsController extends Controller
{
    use ModelForm;

    //抽象方法，返回当前管理的商品类型
    abstract public function getProductType();
    //抽象方法，返回列表应该展示的字段
    abstract protected function customGrid(Grid $grid);
    //抽象方法，返回表单额外字段
    abstract protected function customForm(Form $form);

    public function index(Content $content)
    {
        return $content
            ->header(Product::$typeMap[$this->getProductType()].'列表')
            ->body($this->grid());
    }

    public function edit($id, Content $content)
    {
        return $content
            ->header('编辑'.Product::$typeMap[$this->getProductType()])
            ->body($this->form()->edit($id));
    }

    public function create(Content $content)
    {
        return $content
            ->header('创建'.Product::$typeMap[$this->getProductType()])
            ->body($this->form());
    }

    protected  function grid()
    {
        // 定义一个抽象方法，各个类型的控制器将实现本方法来定义列表应该展示哪些字段
        return Admin::grid(Product::class, function (Grid $grid) {
            $grid->model()->where('type',$this->getProductType())->orderBy('id', 'desc');
            $grid->id('ID')->sortable();

            $this->customGrid($grid);//传对象，改变原值，nice

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
            $form->hidden('type', '商品类型')->default($this->getProductType())->rules('required');
            $form->text('title', '商品名称')->rules('required');
            $form->text('long_title', '商品长标题')->rules('required');
            $form->select('category_id', '商品分类')
                ->options($options)->default($category_id)->rules('required');

            // 创建一个选择图片的框
            $form->image('image', '封面图片')->rules('required|image');

            // 创建一个富文本编辑器
            $form->editor('description', '商品描述')->rules('required');

            // 创建一组单选框
            $form->radio('on_sale', '上架')->options(['1' => '是', '0'=> '否'])->default('0');

            $this->customForm($form); //传对象，改变原值，nice

            // 直接添加一对多的关联模型
            $form->hasMany('pro_attr', '商品属性', function (Form\NestedForm $form) {
                $form->text('name', '属性名称')->placeholder('请输入该商品具有的属性名称，例如:颜色')->rules('required');
                $form->radio('hasmany', '属性是否可选')->help('可选代表用户可以选择的属性，比如衣服这个商品的可选属性就是大小、颜色,这些是用户可以选的，唯一的属性比如衣服的生产厂家、生产日期，这样的属性，用户没得选，唯一属性会列在商品介绍中，供用户参考')
                    ->options(['1' => '可选', '0'=> '唯一'])->default('1')->rules('required');
                $form->text('val', '属性值')->placeholder('当属性为唯一时填写该项，可选属性不用填写该项（可选属性值在设置库存时再填写）');
                $form->radio('is_search', '是否参与分面搜索')->options(['0'=>'不参与', '1'=>'参与'])->default('1');
            });

        });
    }

    public function destroy($id)
    {
        if ($this->form()->destroy($id)) {

            Attribute::where('product_id',$id)->delete();
            ProductAttribute::where('product_id',$id)->delete();
            ProductSku::where('product_id',$id)->delete();
            $params = [
                'index' => 'products',
                'type'  => '_doc',
                'id'    => $id,
            ];
            //商品删除的同时把ES里的数据也删了
            app('es')->delete($params);

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