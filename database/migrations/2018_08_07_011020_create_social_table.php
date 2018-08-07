<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSocialTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('social_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('openid', 100);
            $table->string('unionid', 100)->nullable();
            $table->string('session_key', 100)->nullable();
            $table->string('type', 20);
            $table->string('avatar', 500);
            $table->string('nickname', 50);
            $table->string('gender', 10);
            $table->unsignedInteger('user_id')->nullable();
            $table->string('extra', 1000);
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
        Schema::dropIfExists('social_infos');
    }
}
