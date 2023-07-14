<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('')->index()->comment('角色组名');
            $table->foreignId('admin_role_group_id')->index()->default(0)->comment('角色组ID');
            $table->foreignId('customer_id')->default(0)->comment('客户ID');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['customer_id', 'name'], 'admin_role_groups_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_roles');
    }
}
