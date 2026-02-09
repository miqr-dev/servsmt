<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddZugangChatGptOnlinemarketingItem extends Migration
{
  public function up()
  {
    $exists = DB::table('onlinemarketing_items')
      ->where('name', 'Zugang Chat GPT')
      ->exists();

    if (!$exists) {
      DB::table('onlinemarketing_items')->insert([
        'name' => 'Zugang Chat GPT',
        'created_at' => now(),
        'updated_at' => now(),
      ]);
    }
  }

  public function down()
  {
    DB::table('onlinemarketing_items')
      ->where('name', 'Zugang Chat GPT')
      ->delete();
  }
}
