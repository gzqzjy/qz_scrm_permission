<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminDepartmentsTable extends Migration
{
    protected $connection = 'common';
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('')->index()->comment('部门名称');
            $table->foreignId('customer_id')->default(0)->comment('客户ID');
            $table->foreignId('pid')->default(0)->comment('上级部门');
            $table->unsignedInteger('level')->default(0)->comment('部门等级');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['customer_id', 'name'], 'admin_departments_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_departments');
    }
}
