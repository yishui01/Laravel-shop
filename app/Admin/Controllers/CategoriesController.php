<?php

namespace App\Admin\Controllers;

use App\Models\Category;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\Facades\DB;

class CategoriesController extends Controller
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

            $content->body($this->form($id)->edit($id));
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
        return Admin::grid(Category::class, function (Grid $grid) {
            $grid->id('ID')->sortable();
            $grid->name('分类名称');
            $grid->path('path');
            $grid->level('层级');
            $grid->isshow('是否显示')->display(function ($isshow){
                return $isshow == 'A' ? '是' : '否';
            });
            $grid->parent_id('上级分类')->display(function ($parent_id){
                if (!$parent_id) {
                    return '顶级分类';
                } else {
                    $res = Category::where('id',$parent_id)->pluck('name')->toArray();
                    if(empty($res)) {
                        return '上级分类已被删除';
                    } else {
                        return $res[0];
                    }
                }
            });
            $grid->score('排序权重');
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
        $options_data = $category->getCateList();
        $options = ['0'=>'顶级分类'];
        foreach ($options_data as $k => $v) {
            if($v['id'] == $id)continue;
            $options[$v['id']] = $v['text'];
        }
        $parent_id = (int)($id ? Category::find($id)->parent_id : 0);
        return Admin::form(Category::class, function (Form $form) use ($options, $parent_id) {
            $form->display('id', 'ID');
            $form->select('parent_id', '上级分类')->options($options)->default($parent_id);
            $form->text('name','分类名称');

            $form->text('score','排序分值')->default(0);
            $form->radio('isshow', '是否显示')->options(['A'=>'显示','B'=>'不显示'])->default('B');
            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }

    public function destroy($id)
    {
        $cate = new Category();
        $child_id = $cate->getChildren($id);
        array_push($child_id, $id);
        $cate->whereIn('id', $child_id)->delete();
        return response()->json([
            'status'  => true,
            'message' => trans('admin.delete_succeeded'),
        ]);
    }


}
