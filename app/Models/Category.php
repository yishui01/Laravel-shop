<?php

namespace App\Models;

use App\Exceptions\InvalidRequestException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class Category extends Model
{
    public $fillable = ['name','score','parent_id','isshow', 'is_directory', 'path', 'level'];

    public function product()
    {
        $this->hasMany(Product::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    //获取所有祖先类目的ID值
    public function getPathIdsAttribute()
    {
        //去除两端的 '-', 再打断成数组,再去除空值,再删除最后一个元素（因为最后一个元素为自己本身ID）
        $all_ids = array_filter(explode('-', trim($this->path, '-')));
        array_pop($all_ids);
        return $all_ids;
    }

    //获取所有祖先类目并按层级排序
    public function getAncestorsAttribute()
    {
        return Category::query()
            ->whereIn('id', $this->path_ids)
            ->orderBy('level', 'asc')
            ->get();
    }

    //获取所有子类目id，返回数组
    public function getAllChildrenIdAttribute()
    {
        return Category::query()
            ->where('path', 'like', $this->path.'%')
            ->pluck('id')->toArray();
    }

    //获取商品全名（祖先1-祖先2-...-商品本身名）
    public function getFullNameAttribute()
    {
        return $this->ancestors
            ->pluck('name')
            ->push($this->name)
            ->implode('-');
    }

    //显示已经启用的类目
    public function scopeShow($query)
    {
        return $query->where('isshow', '=', 'A');
    }


    /**获取所有的分类列表
     * @param bool $islevel 是否返回层级标志
     * @param bool $showTop 是否在结果中加入顶级分类
     * @return array
     */
    public function getCateList($islevel = false, $showTop = true)
    {
        if ($islevel) {
           $res = $this->addLevel(Category::select(DB::raw('id,name as text,parent_id'))->get()->toArray());
            $arr = ['id'=>0, 'text'=>'顶级分类', 'parent_id'=>0];
        } else {
            $res = Category::select(DB::raw('id,name as text'))->get()->toArray();
            $arr = ['id'=>0, 'text'=>'顶级分类'];
        }
        if ($showTop) array_push($res, $arr);

        return $res;
    }

    //将分类数据按照parent_id添加层级标志
    public function addLevel($data = [])
    {
        if (empty($data)) return $data;
        $res = $this->_addLevel($data, 0, 0, true);
        return $res;
    }

    private function _addLevel($data = [], $parent_id = 0, $level = 0, $refresh = false)
    {
        if (empty($data)) return $data;

        static $res = [];

        if ($refresh) {
            $res = [];
        }

        foreach ($data as $k => &$v) {
            if ($v['parent_id'] == $parent_id) {
                $v['level'] = $level;
                $res[] = $v;
                $this->_addLevel($data, $v['id'], $level+1);
            }

        }

        return $res;
    }

    /**
     * 获取该分类下的所有子分类数据，返回树形结构
     * @param int $parentid
     * @param int $obj
     * @return array
     */
    public function getTree($parentid = 0, $data = null)
    {
        if (is_null($data)) {
            //筛选出显示的分类
            $data = Category::show()->get()->toArray();
        }
        $a = 0;
        return $this->_getTree($data,$parentid, $a, true);

    }

    private function _getTree($data, $parentid, &$obj = 0, $refresh = false)
    {
        static $res = [];

        if($refresh){
            $res = [];
        }

        foreach ($data as $k=>&$v)
        {
            if ($v['parent_id'] == $parentid) {
                if($obj === 0) {
                    $this->_getTree($data, $v['id'], $v);
                    $res[] = $v;
                } else {
                    $this->_getTree($data, $v['id'], $v);
                    $obj['children'][] = $v;
                }
            }
        }
        return $res;
    }



    /**返回该分类下的所有子分类ID数组
     * @param int $id 分类ID
     */
    public function getChildren($id = 0)
    {
        if ((int)$id <= 0)throw new InvalidRequestException('分类ID错误');
        $data = Category::all();
        return $this->_getChildren($data, $id, true);
    }

    private function _getChildren($data, $parent_id, $refresh = false)
    {
        static $res = [];
        if ($refresh) {
           $res = [];
        }
        foreach ($data as $k=>$v) {
            if ($v['parent_id'] == $parent_id) {
                $res[] = $v['id'];
                $this->_getChildren($data, $v['id']);
            }
        }
        return $res;
    }




}
