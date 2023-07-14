<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminCategoryDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_category_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->default(0)->comment('品类ID');
            $table->foreignId('admin_department_id')->default(0)->index()->comment('部门ID');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['category_id', 'admin_department_id'], 'admin_category_departments_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_category_departments');
    }
}
