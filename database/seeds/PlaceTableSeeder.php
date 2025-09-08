<?php


use App\Place;
use Illuminate\Database\Seeder;

class PlaceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Place::insert([
            ["id"=>1,"pnname"=>"Erfurt"],
            ["id"=>2,"pnname"=>"Suhl"],
            ["id"=>3,"pnname"=>"Leipzig"],
            ["id"=>4,"pnname"=>"Dresden"],
            ["id"=>5,"pnname"=>"Chemnitz"],
            ["id"=>6,"pnname"=>"Berlin"]
        ]);
    }
}
