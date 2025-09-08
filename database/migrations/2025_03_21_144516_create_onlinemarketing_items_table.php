<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOnlinemarketingItemsTable extends Migration
{
  public function up()
  {
    Schema::create('onlinemarketing_items', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->timestamps();
      $table->softDeletes();
    });
  }
  public function down()
  {
    Schema::dropIfExists('onlinemarketing_items');
  }
}
