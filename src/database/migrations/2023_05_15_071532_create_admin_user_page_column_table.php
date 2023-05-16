<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminUserPageColumnTable extends Migration
{
    protected $connection = 'common';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_user_page_column', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_customer_subsystem_id')->default(0)->comment('客户系统管理员ID');
            $table->foreignId('admin_column_id')->default(0)->comment('页面列ID');
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
        Schema::dropIfExists('admin_user_page_column');
    }
}
