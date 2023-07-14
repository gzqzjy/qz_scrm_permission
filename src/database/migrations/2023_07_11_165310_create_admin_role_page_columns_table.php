<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminRolePageColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_role_page_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_role_id')->default(0)->comment('角色ID');
            $table->foreignId('admin_page_column_id')->default(0)->index()->comment('页面列ID');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['admin_role_id', 'admin_page_column_id'], 'admin_role_page_columns_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_role_page_columns');
    }
}
