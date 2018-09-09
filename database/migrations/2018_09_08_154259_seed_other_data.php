<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SeedOtherData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //填充轮播图数据
        $banner_data = [
            'place' =>'mini-index',
            'type'  => 'A',
            'product_id' => 1,
            'url'   => env('APP_URL').'/img/iphone7.jpg',
            'title' => 'iphone7',
            'link'  => '',
            'isshow'=> '1'
        ];
        DB::table('banners')->insert($banner_data);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('banners')->truncate();
    }
}
