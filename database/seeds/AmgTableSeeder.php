<?php

use App\Amg;
use Illuminate\Database\Seeder;

class AmgTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Amg::insert([
            ["id"=> 1,"name"=>"defekt, Reparatur unwirtschaftlich"],
            ["id"=> 2,"name"=>"defekt, Reparatur nicht möglich"],
            ["id"=> 3,"name"=>"moralisch verschlissen"],
            ["id"=> 4,"name"=>"Diebstahl"],
            ["id"=> 5,"name"=>"Verlust"],
            ["id"=> 6,"name"=>"Sonstiger Grund"]
        ]);
    }
}
