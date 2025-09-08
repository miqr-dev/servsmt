<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvLastNumbersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inv_last_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id');
            $table->integer('last_inv_num')->default('0');
            $table->string('suffix',2)->default('IT');
            $table->timestamps();

            $table->foreign('location_id')->references('id')->on('locations');
            $table->index(['last_inv_num']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inv_last_numbers');
    }
}
