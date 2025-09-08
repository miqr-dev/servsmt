<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inv_items', function (Blueprint $table) {
            $table->id();
            $table->date('dateupd')->comment('Datem der letzten Änderung');
            $table->string('invnr',20)->unique();
            $table->foreignId('room_id')->nullable();
            $table->string('gname',50)->nullable();
            $table->string('sn',50)->nullable();
            $table->foreignId('gart_id')->nullable();
            $table->string('gtyp',50)->nullable();
            $table->timestamps();
            $table->foreign('room_id')->references('id')->on('inv_rooms');
            $table->foreign('gart_id')->references('id')->on('garts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inv_items');
    }
}
