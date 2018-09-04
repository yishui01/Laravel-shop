<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UsersAddOpenid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('wx_unionid')->nullable();
            $table->string('wx_web_openid')->nullable();
            $table->string('wx_mini_openid')->nullable();
            $table->string('session_key')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('wx_unionid');
            $table->dropColumn('wx_web_openid');
            $table->dropColumn('wx_mini_openid');
            $table->dropColumn('session_key');
        });
    }
}
