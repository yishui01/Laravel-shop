<?php

namespace App\Admin\Controllers;

use App\Models\WebInfo;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class WebInfosController extends Controller
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
        return Admin::grid(WebInfo::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->web_name('站点名称');
            $grid->web_email('站点邮箱');
            $grid->web_description('站点描述（放head头中的）');
            $grid->web_keywords('站点关键字（放head头中的）');
            $grid->web_notice('站点公告')->editable();
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
        return Admin::form(WebInfo::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('web_name', '网站名称');
            $form->text('web_email', '网站邮箱');
            $form->text('web_description', 'description');
            $form->text('web_keywords', 'keywords');
            $form->editor('web_notice', '网站公告');
            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }
}
