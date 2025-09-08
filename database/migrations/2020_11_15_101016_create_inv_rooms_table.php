<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inv_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('place_id')->nullable();
            $table->foreignId('location_id')->nullable();
            $table->string('etage',50)->default('0')->nullable();
            $table->string('rname',50)->nullable();
            $table->string('altrname',50)->nullable();
            $table->string('ad_ou',500)->nullable();
            $table->timestamps();

            $table->foreign('place_id')->references('id')->on('places');
            $table->foreign('location_id')->references('id')->on('locations');
            $table->index(['rname']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inv_rooms');
    }
}
