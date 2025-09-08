<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKorsosTable extends Migration
{
  public function up()
  {
    Schema::create('korsos', function (Blueprint $table) {
      $table->id();
      $table->string('submitter_name')->nullable();
      $table->integer('submitter')->nullable();
      $table->string('submitter_standort')->nullable();
      $table->string('submitter_adresse')->nullable();
      $table->tinyInteger('priority')->default(2);
      $table->integer('ticket_status_id')->default(1);
      $table->string('tel_number')->nullable();
      $table->string('problem_type');
      $table->string('onlinemarketing_item')->nullable();
      $table->unsignedBigInteger('assignedTo')->nullable();
      $table->string('done_by')->nullable();
      $table->integer('location_id')->nullable();
      $table->text('notizen')->nullable();
      $table->foreignId('onlinemarketing_item_id')->nullable()->constrained('onlinemarketing_items');
      $table->foreignId('zertifizierung_item_id')->nullable()->constrained('zertifizierung_items');
      $table->foreignId('massnahme_id')->nullable()->constrained('massnahmes');
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
    Schema::dropIfExists('korsos');
  }
}
