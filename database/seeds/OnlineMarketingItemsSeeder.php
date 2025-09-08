<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OnlineMarketingItemsSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    $items = [];
    for ($i = 1; $i <= 20; $i++) {
      $items[] = ['name' => "Online Marketing Item Option {$i}"];
    }
    DB::table('onlinemarketing_items')->insert($items);
  }
}
