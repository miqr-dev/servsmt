<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCityNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('city_notes', function (Blueprint $table) {
            $table->id();
            $table->longText('berlin')->nullable();
            $table->longText('berlin2')->nullable();
            $table->longText('chemnitz')->nullable();
            $table->longText('dresden')->nullable();
            $table->longText('leipzig')->nullable();
            $table->longText('suhl')->nullable();
            $table->longText('erfurt')->nullable();
            $table->longText('döbeln ')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('city_notes');
    }
}
