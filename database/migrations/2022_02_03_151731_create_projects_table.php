<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('projects', function (Blueprint $table) {
      $table->id();
      $table->integer('who_created')->nullable();
      $table->integer('assignedTo')->nullable();
      $table->foreignId('ticket_id')->nullable()->constrained()->onDelete('cascade');
      $table->foreignId('location_id')->nullable()->constrained()->onDelete('cascade');
      $table->string('name');
      $table->text('description')->nullable();
      $table->date('start_date')->nullable();
      $table->date('end_date')->nullable();
      $table->string('status');
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
    Schema::dropIfExists('projects');
  }
}
