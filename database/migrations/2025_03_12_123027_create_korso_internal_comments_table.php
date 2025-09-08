<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKorsoInternalCommentsTable extends Migration
{
  public function up()
  {
    Schema::create('korso_internal_comments', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('korso_id');  // Ticket ID
      $table->unsignedBigInteger('user_id');   // Commenter
      $table->text('comment');                 // Comment text
      $table->boolean('is_deleted')->default(false); // Soft delete flag
      $table->timestamps();

      // Foreign Keys
      $table->foreign('korso_id')->references('id')->on('korsos')->onDelete('cascade');
      $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('korso_internal_comments');
  }
}
