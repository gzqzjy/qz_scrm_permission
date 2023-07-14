<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminRoleGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_role_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('')->index()->comment('角色组名');
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
        Schema::dropIfExists('admin_role_groups');
    }
}
