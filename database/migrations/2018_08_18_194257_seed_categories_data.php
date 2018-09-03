<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SeedCategoriesData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $now = \Illuminate\Support\Carbon::now()->toDatetimeString();
        $categories = [
            [
                'name'   => '手机',
                'isshow' => 'A', //A代表显示
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name'   => '服装',
                'isshow' => 'A', //A代表显示
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name'   => '电器',
                'isshow' => 'A', //A代表显示
                'created_at' => $now,
                'updated_at' => $now
            ],
        ];
        DB::table('categories')->insert($categories);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('categories')->truncate();
    }
}
