<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKorsoItemsTable extends Migration
{
  public function up()
  {
    Schema::create('korso_items', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('korso_id');
      $table->string('item_name'); // Stores names like "Wandkalender", "USB-Sticks"
      $table->integer('quantity')->default(1);
      $table->boolean('ordered')->default(false);
      $table->timestamps();

      // Foreign key constraint
      $table->foreign('korso_id')->references('id')->on('korsos')->onDelete('cascade');
    });
  }

  public function down()
  {
    Schema::dropIfExists('korso_items');
  }
}
