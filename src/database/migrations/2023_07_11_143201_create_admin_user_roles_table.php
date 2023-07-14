<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminUserRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_role_id')->default(0)->index()->comment('角色ID');
            $table->foreignId('admin_user_id')->default(0)->comment('管理员ID');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['admin_role_id', 'admin_user_id'], 'admin_user_roles_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_user_roles');
    }
}
