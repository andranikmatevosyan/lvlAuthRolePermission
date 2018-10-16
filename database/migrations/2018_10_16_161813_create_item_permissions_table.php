<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type');
            $table->string('name');
            $table->string('model_name');
            $table->unsignedInteger('model_id');
            $table->timestamps();
        });

        Schema::create('role_has_item_permissions', function (Blueprint $table) {
            $table->integer('item_permission_id')->unsigned();
            $table->integer('role_id')->unsigned();

            $table->primary(['item_permission_id', 'role_id']);
        });

        Schema::table('role_has_item_permissions', function($table) {
            $table->foreign('item_permission_id')->references('id')->on('item_permissions');
            $table->foreign('role_id')->references('id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_permissions');
        Schema::dropIfExists('role_has_item_permissions');
    }
}
