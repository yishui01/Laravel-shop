<?php
namespace App\Http\ViewComposers;

use App\Models\Category;
use Illuminate\View\View;

class CategoryTreeComposer
{
    protected $category;

    // 使用 Laravel 的依赖注入，自动注入我们所需要的 CategoryService 类
    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    // 当渲染指定的模板时，Laravel 会调用 compose 方法
    public function compose(View $view)
    {
        // 使用 with 方法注入变量
        $view->with('categoryTree', $this->category->getTree());
    }
}