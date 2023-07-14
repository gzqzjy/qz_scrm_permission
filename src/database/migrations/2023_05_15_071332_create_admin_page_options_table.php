<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminPageOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_page_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_page_id')->default(0)->comment('页面ID');
            $table->string('name')->default('')->index()->comment('页面操作名');
            $table->string('code')->default('')->index()->comment('页面操作标识');
            $table->boolean('is_show')->default(true)->comment('是否展示');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['admin_page_id', 'code'], 'admin_page_options_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_page_options');
    }
}
