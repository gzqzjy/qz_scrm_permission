<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminDepartmentRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_department_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_department_id')->default(0)->comment('部门ID');
            $table->foreignId('admin_role_id')->default(0)->index()->comment('角色ID');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['admin_department_id', 'admin_role_id'], 'admin_department_roles_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_department_roles');
    }
}
