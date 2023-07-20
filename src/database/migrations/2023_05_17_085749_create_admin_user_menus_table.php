<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminUserMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_user_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->default(0)->index('admin_user_id')->comment('管理员ID');
            $table->foreignId('admin_menu_id')->default(0)->index('admin_menu_id')->comment('菜单ID');
            $table->string('type')->default('')->comment('类型');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['admin_user_id', 'admin_menu_id', 'typ'], 'admin_user_menus_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_user_menus');
    }
}
