<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminUsersTable extends Migration
{
    protected $connection = 'common';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('')->comment('管理员名');
            $table->string('mobile')->default('')->comment('手机号');
            $table->foreignId('customer_id')->default(0)->comment('客户ID');
            $table->string('status')->default('')->comment('状态');
            $table->string('sex')->default('')->comment('性别');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['mobile', 'customer_id'], 'admin_users_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_users');
    }
}
