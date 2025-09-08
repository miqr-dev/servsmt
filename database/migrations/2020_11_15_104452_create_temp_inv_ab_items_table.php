<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTempInvAbItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_inv_ab_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->nullable();
            $table->integer('lfd_num')->nullable();
            $table->string('invnr',50)->nullable();
            $table->foreignId('room_id')->nullable();
            $table->string('gname',50)->nullable();
            $table->string('gtyp',50)->nullable();
            $table->string('sn',50)->nullable();
            $table->foreignId('gart_id')->nullable();
            $table->timestamps();

            $table->foreign('location_id')->references('id')->on('locations');
            $table->foreign('gart_id')->references('id')->on('garts');
            $table->foreign('room_id')->references('id')->on('inv_rooms');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('temp_inv_ab_items');
    }
}
