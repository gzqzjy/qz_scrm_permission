<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminPageColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_page_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_page_id')->default(0)->comment('页面ID');
            $table->string('name')->default('')->index()->comment('页面列名');
            $table->string('code')->default('')->index()->comment('页面列标识');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['admin_page_id', 'code'], 'admin_page_columns_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_page_columns');
    }
}
