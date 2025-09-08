<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePracticeCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('practice_companies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('place_id');
            $table->string('Windows_Username')->nullable();
            $table->string('Windows_Password')->nullable();
            $table->string('Lexware_Username')->nullable();
            $table->string('Lexware_Password')->nullable();
            $table->string('Email_Username')->nullable();
            $table->string('Email_Password')->nullable();
            $table->timestamps();
            $table->foreign('place_id')->references('id')->on('places')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('practice_companies');
    }
}
