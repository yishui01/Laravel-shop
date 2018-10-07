<?php

namespace App\Admin\Controllers;

use App\Models\User;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class UsersController extends Controller
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

            $content->header('用户列表');
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

            $content->header('编辑用户');
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

            $content->header('新增用户');
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
        return Admin::grid(User::class, function (Grid $grid) {

            // 创建一个列名为 ID 的列，内容是用户的 id 字段，并且可以在前端页面点击排序
            $grid->id('ID')->sortable();
            // 创建一个列名为 用户名 的列，内容是用户的 name 字段。下面的 email() 和 created_at() 同理
            $grid->name('用户名');
            $grid->phone('手机号');

            $grid->email('邮箱');

            $grid->email_verified('已验证邮箱')->display(function ($value) {
                return $value ? '是' : '否';
            });

            $grid->created_at('注册时间');
            $grid->updated_at('最后修改时间');

            /*
            // 不在页面显示 `新建` 按钮，因为我们不需要在后台新建用户
            $grid->disableCreateButton();
             $grid->actions(function ($actions) {

                // 不在每一行后面展示删除按钮
                $actions->disableDelete();

                // 不在每一行后面展示编辑按钮
                $actions->disableEdit();
            });
            $grid->tools(function ($tools) {

                // 禁用批量删除按钮
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });
            */


        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(User::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('name','用户名');
            $form->password('password','密码');
            $form->text('phone','手机号');
            $form->email('email');
            $form->radio('email_verified','已验证邮箱')->options(['0' => '未验证', '1'=> '已验证'])->default('0');
            $form->saved(function (Form $form) {
                $user = $form->model();
                $user->password = bcrypt($user->password);
                $user->save();
            });
            /*$form->datetime('created_at');
            $form->datetime('updated_at');*/
        });
    }
}
