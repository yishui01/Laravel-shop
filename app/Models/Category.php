<?php

namespace App\Models;

use App\Exceptions\InvalidRequestException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class Category extends Model
{
    public $fillable = ['name','score','parent_id','isshow'];
    public function product()
    {
        $this->hasMany(Product::class);
    }

    /**获取所有的分类列表
     * @param bool $isTree 是否返回树形结构
     * @param bool $showTop 是否在结果中加入顶级分类
     * @return array
     */
    public function getTreeCateList($isTree = false, $showTop = true)
    {
        if ($isTree) {
           $res = $this->tansfromToTree(Category::select(DB::raw('id,name as text,parent_id'))->get());
            $arr = ['id'=>0, 'text'=>'顶级分类', 'parent_id'=>0];
        } else {
            $res = Category::select(DB::raw('id,name as text'))->get()->toArray();
            $arr = ['id'=>0, 'text'=>'顶级分类'];
        }
        if ($showTop) array_push($res, $arr);

        return $res;
    }

    //将分类数据按照parent_id转换成树形结构
    public function tansfromToTree($data = [])
    {
        if (empty($data)) return $data;
        $res = $this->_tansfromToTree($data, 0, 0, true);
        return $res;
    }

    /**
     * 递归遍历数组，返回树形结构数组
     * @param array $data
     * @param int $parent_id
     * @param int $level
     * @param bool $refresh 是否刷新静态数组（同一次请求内的多次调用需要刷新，否则之前的结果还在里面）
     * @return array
     */
    private function _tansfromToTree($data = [], $parent_id = 0, $level = 0, $refresh = false)
    {
        if (empty($data)) return $data;

        static $res = [];

        if ($refresh) {
            $res = [];
        }

        foreach ($data as $k => &$v) {
            if ($v['parent_id'] == $parent_id) {
                $v['level'] = $level;
                $v['children'] = $this->_tansfromToTree($data, $v['id'], $level+1, false);
                $res[] = $v;
            }
        }

        return $res;

    }

    /**获取分类下的所有子分类
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
