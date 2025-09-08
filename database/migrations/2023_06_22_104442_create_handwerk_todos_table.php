<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHandwerkTodosTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('handwerk_todos', function (Blueprint $table) {
      $table->id();
      $table->integer('ticket_id')->nullable();
      $table->integer('user_id')->nullable();
      $table->string('standort');
      $table->string('title');
      $table->text('body');
      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('handwerk_todos');
  }
}
