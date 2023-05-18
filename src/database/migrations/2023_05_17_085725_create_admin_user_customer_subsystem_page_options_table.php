<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminUserCustomerSubsystemPageOptionsTable extends Migration
{
    protected $connection = 'common';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_user_customer_subsystem_page_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_customer_subsystem_id')->default(0)->comment('客户系统管理员ID');
            $table->foreignId('admin_page_option_id')->default(0)->comment('页面操作ID');
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
        Schema::dropIfExists('admin_user_customer_subsystem_page_options');
    }
}
