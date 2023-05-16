<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminPageCustomersTable extends Migration
{
    protected $connection = 'common';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_page_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_page_id')->default(0)->comment('页面ID');
            $table->foreignId('customer_id')->default(0)->comment('客户ID');
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
        Schema::dropIfExists('admin_page_customers');
    }
}
