<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKorsoAttachmentsTable extends Migration
{
  public function up()
  {
    Schema::create('korso_attachments', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('korso_id');
      $table->string('file_path'); // Path to stored file
      $table->string('file_type'); // Image/PDF
      $table->timestamps();

      // Foreign key constraint
      $table->foreign('korso_id')->references('id')->on('korsos')->onDelete('cascade');
    });
  }

  public function down()
  {
    Schema::dropIfExists('korso_attachments');
  }
}
