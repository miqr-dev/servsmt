<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUmfragesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('umfrages', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('place_id'); // Foreign key to places table
      $table->unsignedBigInteger('umcategory_id');
      $table->string('title');
      $table->string('url');
      $table->timestamps();

      $table->foreign('place_id')->references('id')->on('places');
      $table->foreign('umcategory_id')->references('id')->on('umcategories');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('umfrages');
  }
}
