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

class CrowdfundingProductsController extends CommonProductsController
{
    // 移除 HasResourceActions
    public function getProductType()
    {
        return Product::TYPE_CROWDFUNDING;
    }

    //众筹商品的列表显示字段
    protected function customGrid(Grid $grid)
    {
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
    }

    //众筹商品的表单额外字段
    protected function customForm(Form $form)
    {
        $form->text('crowdfunding.target_amount', '众筹目标金额')->rules('required|numeric|min:0.01');
        $form->datetime('crowdfunding.end_at', '众筹结束时间')->rules('required|date');
    }

}
