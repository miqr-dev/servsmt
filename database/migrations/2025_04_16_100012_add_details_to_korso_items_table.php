<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDetailsToKorsoItemsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('korso_items', function (Blueprint $table) {
      Schema::table('korso_items', function (Blueprint $table) {
        $table->json('details')->nullable();
      });
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('korso_items', function (Blueprint $table) {
      //
    });
  }
}
