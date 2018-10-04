<?php

namespace App\Observer;

use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategoryObserver
{
    //填充 path，isdirectory，和level字段
    public function saved(Category $category)
    {
        if(!$category->parent_id) {
            //如果parent_id为0
            $category->level = 0;
            $category->path = '-'.$category->id.'-';
        } else {
            $category->level = $category->parent->level + 1;
            $category->path = $category->parent->path.$category->id.'-';
        }
        DB::table('categories')->where('id', $category->id)->update([
            'level' => $category->level,
            'path'  => $category->path,
        ]);
    }
}