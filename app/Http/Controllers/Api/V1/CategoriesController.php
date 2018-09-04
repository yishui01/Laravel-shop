<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Category;
use App\Transformers\CategoryTransformer;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    //分类列表接口
    public function index()
    {
        return $this->response->collection(Category::show()->orderBy('score', 'desc')->get(),
            new CategoryTransformer())->setStatusCode($this->success_code);
    }
}
