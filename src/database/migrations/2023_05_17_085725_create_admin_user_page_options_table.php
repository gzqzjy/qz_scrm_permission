<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminUserPageOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_user_page_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->default(0)->comment('管理员ID');
            $table->foreignId('admin_page_option_id')->default(0)->index('admin_page_option_id')->comment('页面操作ID');
            $table->string('type')->default('')->comment('类型');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['admin_user_id', 'admin_page_option_id', 'type'], 'admin_user_page_options_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_user_page_options');
    }
}
