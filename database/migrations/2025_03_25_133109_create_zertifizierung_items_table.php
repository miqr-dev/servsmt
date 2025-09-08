<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZertifizierungItemsTable extends Migration
{
  public function up()
  {
    Schema::create('zertifizierung_items', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->boolean('location_needed')->default(false);
      $table->boolean('massnahme_needed')->default(false);
      $table->timestamps();
      $table->softDeletes();
    });
  }

  public function down()
  {
    Schema::dropIfExists('zertifizierung_items');
  }
}
