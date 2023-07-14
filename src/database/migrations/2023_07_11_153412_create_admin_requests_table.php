<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_page_option_id')->default(0)->comment('页面操作ID');
            $table->string('name')->default('')->index()->comment('请求名称');
            $table->string('code')->default('')->index()->comment('请求标识');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['admin_page_option_id', 'code'], 'admin_requests_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_requests');
    }
}
