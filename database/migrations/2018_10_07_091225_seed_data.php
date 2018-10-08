<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //填充商品模块的五个表的数据 分类表、商品表、属性表、属性值表，SKU表
        //分类表
        $categories_data = [
            [
                'id'        => 1,
                'name'      => '手机/数码',
                'parent_id' => 0,
                'level'     => 0,
                'path'      => '-1-'
            ],
            [
                'id'        => 2,
                'name'      => '手机配件',
                'parent_id' => 1,
                'level'     => 1,
                'path'      => '-1-2-'
            ],
            [
                'id'        => 3,
                'name'      => '手机',
                'parent_id' => 1,
                'level'     => 1,
                'path'      => '-1-3-'
            ],
            [
                'id'        => 4,
                'name'      => '电脑/办公',
                'parent_id' => 0,
                'level'     => 0,
                'path'      => '-4-'
            ],

            [
                'id'        => 5,
                'name'      => '电脑',
                'parent_id' => 4,
                'level'     => 0,
                'path'      => '-4-5-'
            ],

            [
                'id'        => 6,
                'name'      => '笔记本',
                'parent_id' => 5,
                'level'     => 1,
                'path'      => '-4-5-6-'
            ],
            [
                'id'        => 7,
                'name'      => '台式机',
                'parent_id' => 5,
                'level'     => 1,
                'path'      => '-4-5-7-'
            ],
            [
                'id'        => 8,
                'name'      => '电脑配件',
                'parent_id' => 4,
                'level'     => 1,
                'path'      => '-4-8-'
            ],
            [
                'id'        => 9,
                'name'      => '硬盘',
                'parent_id' => 8,
                'level'     => 2,
                'path'      => '-4-8-9-'
            ],
        ];
        //填充分类表
        \App\Models\Category::insert($categories_data);

        //商品表
        $products_data = [
            //普通商品
            [
                'id'          => 1,
                'title'       => 'Apple 苹果 iPhone XS Max (A2104) 手机 全网通 深空灰 256G',
                'long_title'  => 'Apple 苹果 iPhone XS Max (A2104) 手机 全网通 深空灰 256G 移动联通电信4G手机 双卡双待',
                'description' => '新品上市',
                'image'       => 'https://img10.360buyimg.com/n1/s450x450_jfs/t1/4809/12/3501/164097/5b997dd5Eb8a466ef/f04fff1f415df9ec.jpg',
                'on_sale'     => 1, //是否上架
                'price'       => 0, //最低SKU价格
                'category_id' => 3,
                'type'        => \App\Models\Product::TYPE_NORMAL
            ],
            [
                'id'          => 2,
                'title'       => '华为（HUAWEI） 华为Nova3 手机 蓝楹紫 全网通6GB+128GB',
                'long_title'  => '华为（HUAWEI） 华为Nova3 手机 蓝楹紫 全网通6GB+128GB',
                'description' => '新品上市',
                'image'       => 'https://img14.360buyimg.com/n1/s450x450_jfs/t21529/44/2419491361/453934/e2052e50/5b55339aN26b57a5f.jpg',
                'on_sale'     => 1, //是否上架
                'price'       => 0, //最低SKU价格
                'category_id' => 3,
                'type'        => \App\Models\Product::TYPE_NORMAL
            ],
            //众筹商品
            [
                'id'          => 3,
                'title'       => 'Apple 苹果 iPhone X 全面屏手机 深空灰 全网通 64GB',
                'long_title'  => 'Apple 苹果 iPhone X 全面屏手机 深空灰 全网通 64GB',
                'description' => '新品上市',
                'image'       => 'https://img12.360buyimg.com/n1/s450x450_jfs/t10198/341/2049136605/181101/89253dbc/59ec3325N906f107e.jpg',
                'on_sale'     => 1, //是否上架
                'price'       => 0, //最低SKU价格
                'category_id' => 3,
                'type'        => \App\Models\Product::TYPE_CROWDFUNDING
            ],
            //秒杀商品
            [
                'id'          => 4,
                'title'       => '荣耀9i 4GB+64GB 幻夜黑 移动联通电信4G全面屏手机 双卡双待',
                'long_title'  => '荣耀9i 4GB+64GB 幻夜黑 移动联通电信4G全面屏手机 双卡双待',
                'description' => '新品上市',
                'image'       => 'https://img12.360buyimg.com/n1/s450x450_jfs/t21415/332/642302956/189613/778f2021/5b13cd6cN8e12d4aa.jpg',
                'on_sale'     => 1, //是否上架
                'price'       => 0, //最低SKU价格
                'category_id' => 3,
                'type'        => \App\Models\Product::TYPE_SECKILL
            ],

            //笔记本
            [
                'id'          => 5,
                'title'       => '外星人Alienware17.3英寸Gsync屏游戏笔记本电脑',
                'long_title'  => '外星人Alienware17.3英寸Gsync屏游戏笔记本电脑(Intel八代i7-8750H 16G 256GSSD 1T GTX1070 8G独显 FHD)',
                'description' => '新品上市',
                'image'       => 'https://img14.360buyimg.com/n1/s450x450_jfs/t26914/354/282602519/272244/4490284/5b8c95a6Ne12b4986.jpg',
                'on_sale'     => 1, //是否上架
                'price'       => 0, //最低SKU价格
                'category_id' => 6,
                'type'        => \App\Models\Product::TYPE_NORMAL
            ],
            [
                'id'          => 6,
                'title'       => '机械革命(MECHREVO)X8Ti Plus 144Hz72% GTX1060',
                'long_title'  => '机械革命(MECHREVO)X8Ti Plus 144Hz72% GTX1060 17.3英寸窄边游戏笔记本i5-8300H 8G 128G+1T Office2016',
                'description' => '新品上市',
                'image'       => 'https://img12.360buyimg.com/n1/s450x450_jfs/t1/2541/35/9769/75762/5bad9c2bE080a1ce5/6ce772271659603d.jpg',
                'on_sale'     => 1, //是否上架
                'price'       => 0, //最低SKU价格
                'category_id' => 6,
                'type'        => \App\Models\Product::TYPE_SECKILL
            ],
            [
                'id'          => 7,
                'title'       => '希捷(SEAGATE)酷鱼系列 3TB 7200转64M SATA3',
                'long_title'  => '希捷(SEAGATE)酷鱼系列 3TB 7200转64M SATA3 台式机机械硬盘(ST3000DM008)',
                'description' => '新品上市',
                'image'       => 'https://img14.360buyimg.com/n1/s450x450_jfs/t17569/288/2405733938/132625/31417726/5af2e1d0N5051a5b4.jpg',
                'on_sale'     => 1, //是否上架
                'price'       => 0, //最低SKU价格
                'category_id' => 9,
                'type'        => \App\Models\Product::TYPE_NORMAL
            ],
            [
                'id'          => 8,
                'title'       => '西部数据(WD)蓝盘 3TB SATA6Gb/s 64MB 台式机械硬盘(WD30EZRZ)',
                'long_title'  => '西部数据(WD)蓝盘 3TB SATA6Gb/s 64MB',
                'description' => '新品上市',
                'image'       => 'https://img12.360buyimg.com/n1/s450x450_jfs/t7174/332/5833654/169864/2056a17b/597b078dN7e9dcdb1.jpg',
                'on_sale'     => 1, //是否上架
                'price'       => 0, //最低SKU价格
                'category_id' => 9,
                'type'        => \App\Models\Product::TYPE_NORMAL
            ],
            [
                'id'          => 9,
                'title'       => '第一卫【送钢化膜】iPhoneX/XS手机壳',
                'long_title'  => '第一卫【送钢化膜】iPhoneX/XS手机壳苹果xs max手机保护套透明全包防摔超薄硅胶软套磨砂 【X 专用】透明壳★送钢化膜',
                'description' => '新品上市',
                'image'       => 'https://img12.360buyimg.com/n1/jfs/t1/352/12/3787/400037/5b997256E416f7e45/e60465ec78715084.jpg',
                'on_sale'     => 1, //是否上架
                'price'       => 0, //最低SKU价格
                'category_id' => 2,
                'type'        => \App\Models\Product::TYPE_NORMAL
            ],

        ];
        //填充商品表
        \App\Models\Product::insert($products_data);
        //填充秒杀表
        \App\Models\SeckillProduct::insert([
            [
                'product_id' => 4,
                'start_at'   => '2018-10-01 00:00:00',
                'end_at'     => '2019-9-20 00:00:00',
            ],
            [
                'product_id' => 6,
                'start_at'   => '2019-10-01 00:00:00',
                'end_at'     => '2028-10-01 00:00:00',
            ],
            ]);
        //填充众筹表
        \App\Models\CrowdfundingProduct::insert([
            'product_id'     => 3,
            'target_amount'  => 300000,
            'end_at'   => '2019-10-01 00:00:00',
        ]);

        //商品-属性表
        $product_attributes_data = [
            //iphonexs
            [
                'product_id'     => 1,
                'name'           => '颜色',
                'hasmany'        => '1', //属性是否可选
                'val'            => '',
                'test_val'       => [ //属性可选值，该表中无该字段，此处用于数据填充
                    '金色', '银色', '深空灰'
                ]
            ],
            [
                'product_id'     => 1,
                'name'           => '版本',
                'hasmany'        => '1',//属性是否可选
                'val'            => '',
                'is_search'      => 0,  //是否参与分面搜索，0为不参与1为参与，参与了的属性才会显示在属性搜索面板上，默认为1
                'test_val'       => [
                    '64GB', '256GB', '512GB'
                ]
            ],
            [
                'product_id'     => 1,
                'name'           => '品牌',
                'hasmany'        => '0',
                'val'            => 'Apple',
                'test_val'       => []
            ],
            //华为Nova3
            [
                'product_id'     => 2,
                'name'           => '颜色',
                'hasmany'        => '1',
                'val'            => '',
                'test_val'       => [
                    '亮黑色', '浅艾蓝', '蓝楹紫', '星耀版 樱草金', '相思红色'
                ]
            ],
            [
                'product_id'     => 2,
                'name'           => '版本',
                'hasmany'        => '1',
                'val'            => '',
                'is_search'      => 0,
                'test_val'       => [
                    '全网通(6GB+128GB)', '全网通(6GB+64GB)'
                ]
            ],
            [
                'product_id'     => 2,
                'name'           => '品牌',
                'hasmany'        => '0', //属性是否可选
                'val'            => '华为（HUAWEI）',
                'test_val'       => []
            ],
            //iphoneX
            [
                'product_id'     => 3,
                'name'           => '颜色',
                'hasmany'        => '1',
                'val'            => '',
                'test_val'       => [
                    '深空灰', '银色'
                ]
            ],
            [
                'product_id'     => 3,
                'name'           => '版本',
                'hasmany'        => '1',
                'val'            => '',
                'is_search'      => 0,
                'test_val'       => [
                    '64GB', '256GB', '64GB+一年碎屏险套餐'
                ]
            ],
            [
                'product_id'     => 3,
                'name'           => '品牌',
                'hasmany'        => '0', //属性是否可选
                'val'            => 'Apple',
                'test_val'       => []
            ],
            //荣耀9i
            [
                'product_id'     => 4,
                'name'           => '颜色',
                'hasmany'        => '1',
                'val'            => '',
                'test_val'       => [
                    '幻夜黑', '魅海蓝', '梦幻紫','碧玉青'
                ]
            ],
            [
                'product_id'     => 4,
                'name'           => '版本',
                'hasmany'        => '1',
                'val'            => '',
                'is_search'      => 0,
                'test_val'       => [
                    '全网通(4GB 64GB)', '全网通(4GB 128GB)',
                ]
            ],
            [
                'product_id'     => 4,
                'name'           => '套装',
                'hasmany'        => '1',
                'val'            => '',
                'test_val'       => [
                    '官方标配', '耳机套装',
                ]
            ],
            [
                'product_id'     => 4,
                'name'           => '品牌',
                'hasmany'        => '0', //属性是否可选
                'val'            => '华为（HUAWEI）',
                'test_val'       => []
            ],
            [
                'product_id'     => 5,
                'name'           => '型号',
                'hasmany'        => '1',
                'val'            => '',
                'is_search'      => 0,
                'test_val'       => [
                    'i9-8950HK 512SSD 1T GTX1080', 'i9-8950HK 256SSD 1T GTX1080',
                ]
            ],
            [
                'product_id'     => 5,
                'name'           => '品牌',
                'hasmany'        => '0', //属性是否可选
                'val'            => '外星人 (Alienware)',
                'test_val'       => []
            ],
            [
                'product_id'     => 6,
                'name'           => '型号',
                'hasmany'        => '1',
                'val'            => '',
                'is_search'      => 0,
                'test_val'       => [
                    '17.3英寸 【i7/1060/电竞屏】', '17.3英寸 【i7/1050/电竞屏】',
                ]
            ],
            [
                'product_id'     => 6,
                'name'           => '品牌',
                'hasmany'        => '0', //属性是否可选
                'val'            => '机械革命（MECHREVO）',
                'test_val'       => []
            ],
            [
                'product_id'     => 7,
                'name'           => '颜色',
                'hasmany'        => '1',
                'val'            => '',
                'is_search'      => 0,
                'test_val'       => [
                    '希捷酷鱼-办公家用', '希捷酷狼-NAS存储',
                ]
            ],
            [
                'product_id'     => 7,
                'name'           => '容量',
                'hasmany'        => '1',
                'val'            => '',
                'is_search'      => 0,
                'test_val'       => [
                    '1T', '2T','3T','4T','5T',
                ]
            ],
            [
                'product_id'     => 7,
                'name'           => '品牌',
                'hasmany'        => '0', //属性是否可选
                'val'            => '希捷（SEAGATE）',
                'test_val'       => []
            ],
            [
                'product_id'     => 8,
                'name'           => '颜色',
                'hasmany'        => '1',
                'val'            => '',
                'is_search'      => 0,
                'test_val'       => [
                    '红盘 | NAS存储硬盘', '蓝盘 | 日常家用硬盘',
                ]
            ],
            [
                'product_id'     => 8,
                'name'           => '容量',
                'hasmany'        => '1',
                'val'            => '',
                'is_search'      => 0,
                'test_val'       => [
                    '1T', '2T','3T','4T','5T',
                ]
            ],
            [
                'product_id'     => 8,
                'name'           => '品牌',
                'hasmany'        => '0', //属性是否可选
                'val'            => '西部数据（WD）',
                'test_val'       => []
            ],
            [
                'product_id'     => 9,
                'name'           => '颜色',
                'hasmany'        => '1',
                'val'            => '',
                'is_search'      => 0,
                'test_val'       => [
                    '【X 专用】透明壳★送钢化膜',
                    '【Xs 专用】透明壳★送钢化膜',
                ]
            ],
        ];

        //填充商品属性表
        foreach ($product_attributes_data as $k=>$v) {
            $pro_attr = \App\Models\ProductAttribute::create($v);
            //填充商品属性值表
            if($v['hasmany'] == 1) {
                foreach ($v['test_val'] as $v1) {
                    \App\Models\Attribute::create([
                        'product_id' => $v['product_id'],
                        'attr_id'    => $pro_attr->id,
                        'attr_val'   => $v1
                    ]);
                }
            }
        }

        //填充商品SKU表
        $all_products = \App\Models\Product::all();
        foreach ($all_products as $product) {
            $flag = true;
            $pro_attr = \App\Models\ProductAttribute::where([
                ['product_id', '=', $product->id],
                ['hasmany', '=', 1], //只要可选属性
            ])->get();
            foreach ($pro_attr as $attr) {
                //把每个商品的可选属性的属性值做一个全排列，构造SKU
                $res_arr = $this->__dfs($attr->attribute, $flag);
                $flag = false;
            }
            foreach ($res_arr as $k=>$v) {
                \App\Models\ProductSku::create([
                    'title' =>$v['attr_val'],
                    'description' =>'',
                    'price' =>number_format(lcg_value(), 2)+0.01,
                    'stock' =>random_int(100,500),
                    'product_id' =>$product->id,
                    'attributes' =>$v['id'],
                ]);
            }
        }

        //重建ES索引
        \Illuminate\Support\Facades\Artisan::call('es:migrate');
        //创建测试用户
        \App\Models\User::create([
            'name'     => 'test',
            'email'    => '123456@qq.com',
            'password' => bcrypt(123456),
            'email_verified'=>1,
            'phone'   => 13123456789,
            'avatar'  =>'https://ss0.bdstatic.com/70cFuHSh_Q1YnxGkpoWK1HF6hhy/it/u=1468890659,201072083&fm=27&gp=0.jpg'
        ]);
    }


    private function __dfs($arr, $is_flush = false)
    {
        static $res_arr = [];
        if($is_flush){
            $res_arr = [];
        }
        if (empty($res_arr)) {
            foreach ($arr as $k=>$v) {
                $res_arr[] = ['id'=>$v->id, 'attr_val'=>$v->attr_val];
            }
        } else {
            $tmp = $res_arr;
            $res_arr = [];
            foreach ($tmp as $k=>$v) {
                foreach ($arr as $k1=>$v1) {
                    $res_arr[] = ['id'=>$v['id'].','.$v1->id, 'attr_val'=>$v['attr_val'].','.$v1->attr_val];
                }
            }
        }
        return $res_arr;
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::table('categories')->truncate();
        \DB::table('products')->truncate();
        \DB::table('product_skus')->truncate();
        \DB::table('product_attributes')->truncate();
        \DB::table('attributes')->truncate();
    }
}
