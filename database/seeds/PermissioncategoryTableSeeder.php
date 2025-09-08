<?php

use App\Permissioncategory;
use Illuminate\Database\Seeder;

class PermissioncategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permissioncategory::insert([
          ["id"=>1,"name"=>"Geräte"],
          ["id"=>2,"name"=>"Erfassen"],
          ["id"=>3,"name"=>"Drucken"],
          ["id"=>4,"name"=>"Inventur"],
          ["id"=>5,"name"=>"Rollen"],
          ["id"=>6,"name"=>"Benutzer"],
        ]);
    }
}
