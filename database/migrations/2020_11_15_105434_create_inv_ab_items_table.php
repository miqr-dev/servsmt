<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvAbItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inv_ab_items', function (Blueprint $table) {
            $table->id();
            $table->string('invnr',20)->default(null)->unique();
            $table->date('andat')->default(null);
            $table->foreignId('location_id')->nullable();
            $table->float('kp', 8, 2)->nullable();
            $table->foreignId('gart_id')->nullable();
            $table->string('gtyp',50)->nullable();
            $table->string('gname',50)->nullable();
            $table->string('sn',50)->nullable();
            $table->mediumText('notes')->nullable();
            $table->string('path_to_rg',50)->nullable();
            $table->date('ausdat')->nullable();
            $table->foreignId('amg_id')->nullable();
            $table->timestamps();

            $table->foreign('location_id')->references('id')->on('locations');

            $table->foreign('gart_id')->references('id')->on('garts');
            $table->foreign('amg_id')->references('id')->on('amgs');
            $table->index(['gtyp','gname']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inv_ab_items');
    }
}
