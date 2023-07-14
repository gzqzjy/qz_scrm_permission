<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminRoleMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_role_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_role_id')->default(0)->comment('角色ID');
            $table->foreignId('admin_menu_id')->default(0)->index()->comment('菜单ID');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['admin_role_id', 'admin_menu_id'], 'admin_role_menus_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_role_menus');
    }
}
