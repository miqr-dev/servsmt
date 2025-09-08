<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSekGroupUserTable extends Migration
{
  public function up()
  {
    Schema::create('sek_group_user', function (Blueprint $table) {
      $table->unsignedBigInteger('sek_group_id');
      $table->unsignedBigInteger('user_id');

      $table->foreign('sek_group_id')
        ->references('id')->on('sek_groups')
        ->onDelete('cascade');

      $table->foreign('user_id')
        ->references('id')->on('users')
        ->onDelete('cascade');

      $table->primary(['sek_group_id', 'user_id']);
    });
  }

  public function down()
  {
    Schema::dropIfExists('sek_group_user');
  }
}
