<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminUserRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_user_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->default(0)->comment('员工ID');
            $table->foreignId('admin_request_id')->default(0)->comment('请求ID');
            $table->string('type')->default('')->comment('类型');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['admin_user_id', 'admin_request_id', 'type'], 'admin_user_requests_unique');
            $table->index('admin_request_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_user_requests');
    }
}
