<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_pages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('')->index('name_index')->comment('页面名');
            $table->string('code')->default('')->comment('页面标识');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['code'], 'admin_pages_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_pages');
    }
}
