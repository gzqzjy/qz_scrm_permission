<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_menus', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('')->comment('菜单名');
            $table->string('path')->default('')->comment('路由');
            $table->foreignId('parent_id')->default(0)->index()->comment('上级菜单');
            $table->foreignId('sort')->default(0)->comment('排序');
            $table->foreignId('admin_page_id')->default(0)->index()->comment('页面ID');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['name', 'parent_id'], 'admin_menus_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_menus');
    }
}
