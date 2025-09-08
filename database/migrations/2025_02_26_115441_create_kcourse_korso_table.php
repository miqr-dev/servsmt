<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKcourseKorsoTable extends Migration
{
    public function up()
    {
        Schema::create('kcourse_korso', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('korso_id');
            $table->unsignedBigInteger('kcourse_id');
            $table->integer('quantity')->default(1);
            $table->timestamps();

            $table->foreign('korso_id')
                  ->references('id')->on('korsos')
                  ->onDelete('cascade');

            $table->foreign('kcourse_id')
                  ->references('id')->on('kcourses')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('kcourse_korso');
    }
}


