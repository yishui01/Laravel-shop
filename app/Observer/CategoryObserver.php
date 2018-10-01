<?php

namespace App\Observer;

use App\Models\Category;

class CategoryObserver
{
    //填充 path，isdirectory，和level字段
    public function saving(Category $category)
    {
        if(!$category->parent_id) {
            //如果parent_id为0
            $category->level = 0;
            $category->path = '-';
        } else {
            $category->level = $category->parent->level + 1;
            $category->path = $category->parent->path.$category->parent_id.'-';
        }
    }
}