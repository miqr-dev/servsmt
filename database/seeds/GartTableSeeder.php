<?php

use Illuminate\Database\Seeder;
use App\Gart;

class GartTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Gart::insert([
            ["id"=> 1,"name"=>"Server"],
            ["id"=> 2,"name"=>"PC"],
            ["id"=> 3,"name"=>"Notebook"],
            ["id"=> 4,"name"=>"Tablet"],
            ["id"=> 5,"name"=>"Drucker / MFC"],
            ["id"=> 6,"name"=>"Monitor"],
            ["id"=> 7,"name"=>"Switch"],
            ["id"=> 8,"name"=>"Switch (KVM)"],
            ["id"=> 9,"name"=>"Switch (PoE)"],
            ["id"=> 10,"name"=>"Router"],
            ["id"=> 11,"name"=>"AccessPoint"],
            ["id"=> 12,"name"=>"NAS"],
            ["id"=> 13,"name"=>"Beamer"],
            ["id"=> 14,"name"=>"TK-Anlage"],
            ["id"=> 15,"name"=>"Telefon"],
            ["id"=> 16,"name"=>"DECT-Station"],
            ["id"=> 17,"name"=>"Sonstiges"],
            ["id"=> 18,"name"=>"Scanner"]
        ]);
    }
}
