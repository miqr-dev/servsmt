<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMiqrVerwMasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('miqr_verw_mas', function (Blueprint $table) {
            $table->id();
            $table->integer('smt_ul')->default(0);
            $table->integer('stid')->default(0);
            $table->string('ad_ssid',100)->default(0);
            $table->string('dn',300)->default(0);
            $table->tinyInteger('replikation')->default(0);
            $table->foreignId('location_id')->default(0); 
            $table->string('vn',50)->nullable();
            $table->string('nn',50)->nullable();
            $table->string('mail',150)->nullable();
            $table->string('tel',50)->nullable();
            $table->string('fax',50)->nullable();
            $table->string('street',50)->nullable();
            $table->string('plz',50)->nullable();
            $table->string('ort',50)->nullable();
            $table->string('mobil',50)->nullable();
            $table->string('titel',150)->nullable();
            $table->string('taetigkeit',150)->nullable();
            $table->date('lastlogin')->nullable();
            $table->date('dat_aender')->nullable();
            $table->timestamps();
            $table->foreign('location_id')->references('id')->on('locations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('miqr_verw_mas');
    }
}
