<?php

use App\Payer;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
  public function run()
  {
    $this->call(RoleTableSeeder::class);
    $this->call(PermissioncategoryTableSeeder::class);
    $this->call(PermissionTableSeeder::class);
    $this->call(RolePermissionAssignmentSeeder::class);

    //$this->call(UserSeeder::class);
    // $this->call(PlaceTableSeeder::class);
    // $this->call(LocationTableSeeder::class);
    // $this->call(AmgTableSeeder::class);
    // $this->call(GartTableSeeder::class);
    // $this->call(OnlineMarketingItemsSeeder::class);
    // $this->call(MassnahmeSeeder::class);
    // $this->call(ZertifizierungItemSeeder::class);

  }
}
