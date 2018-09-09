<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->increments('id');
            $table->string('place', 20)->default('mini-index')
                ->comment('mini-index为小程序首页轮播，pc-index为pc首页轮播');
            $table->string('type', 20)->default('A')->comment('A为内部商品链接，B为外部链接');
            $table->unsignedInteger('product_id')->default(0)->comment('type为A时，跳转的商品ID');
            $table->string('url', 300);
            $table->string('title', 50)->nullable();
            $table->string('link', 300)->nullable();
            $table->string('isshow', 10)->default('0');
            $table->integer('sort')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('banners');
    }
}
