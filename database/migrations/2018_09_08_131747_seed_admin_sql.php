<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedAdminSql extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $content = file_get_contents(app_path().'/../admin.sql');
        $arr = explode(';', $content);
        foreach ($arr as $k=>$v) {
            if (strpos($v, 'INSERT') === false) {
                \DB::query($v);
            } else {
                \DB::insert($v);
            }

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::table('admin_menu')->truncate();
        \DB::table('admin_operation_log')->truncate();
        \DB::table('admin_permissions')->truncate();
        \DB::table('admin_role_menu')->truncate();
        \DB::table('admin_role_permissions')->truncate();
        \DB::table('admin_role_users')->truncate();
        \DB::table('admin_roles')->truncate();
        \DB::table('admin_user_permissions')->truncate();
        \DB::table('admin_users')->truncate();
    }
}
