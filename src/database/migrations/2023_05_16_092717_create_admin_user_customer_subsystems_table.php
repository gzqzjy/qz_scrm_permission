<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminUserCustomerSubsystemsTable extends Migration
{
    protected $connection = 'common';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_user_customer_subsystems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->default(0)->comment('管理员ID');
            $table->foreignId('customer_subsystem_id')->default(0)->comment('客户系统ID');
            $table->string('status')->default('')->comment('状态');
            $table->boolean('administrator')->default(false)->comment('是否超级管理员');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_user_customer_subsystems');
    }
}
