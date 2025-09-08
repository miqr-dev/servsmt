<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSekGroupsTable extends Migration
{
  public function up()
  {
    Schema::create('sek_groups', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('name')->unique();
      $table->string('email');
      $table->timestamps();
    });

    // seed the seven secretary groups:
    DB::table('sek_groups')->insert([
      ['name' => 'Sek Chemnitz', 'email' =>  'Sekretariat_Chemnitz@miqr.de'],
      ['name' => 'Sek Dresden',   'email' => 'Sekretariat_Dresden@miqr.de'],
      ['name' => 'Sek Doebeln',   'email' => 'Sekretariat_Doebeln@miqr.de'],
      ['name' => 'Sek Leipzig',   'email' => 'Sekretariat_Leipzig@miqr.de'],
      ['name' => 'Sek Riesa',     'email' => 'Sekretariat_Riesa@miqr.de'],
      ['name' => 'Sek Suhl',      'email' => 'Sekretariat_Suhl@miqr.de'],
      ['name' => 'Sek Erfurt',    'email' => 'Sekretariat_Erfurt@miqr.de'],
    ]);
  }

  public function down()
  {
    Schema::dropIfExists('sek_groups');
  }
}
