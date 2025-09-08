<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKcoursesTable extends Migration
{
    public function up()
    {
        Schema::create('kcourses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payer_id'); 
            $table->string('name'); 
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('payer_id')->references('id')->on('payers')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('kcourses');
    }
}

