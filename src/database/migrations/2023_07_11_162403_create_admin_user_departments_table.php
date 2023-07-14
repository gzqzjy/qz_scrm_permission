<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminUserDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_user_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->default(0)->comment('员工ID');
            $table->foreignId('admin_department_id')->default(0)->index('admin_department_id')->comment('部门ID');
            $table->boolean('administrator')->default(0)->comment('');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['admin_user_id', 'admin_department_id'], 'admin_user_departments_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_user_departments');
    }
}
