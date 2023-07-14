<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminRoleRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_role_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_role_id')->default(0)->comment('');
            $table->foreignId('admin_request_id')->default(0)->index()->comment('');
            $table->string('type')->default('')->comment('类型 SELF.自己 THIS.自己部门 PEER.同级部门 CHILDREN.下级部门 UNDEFINED.未定义');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['admin_role_id', 'admin_request_id'], 'admin_role_requests_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_role_requests');
    }
}
