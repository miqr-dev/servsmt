<?php

use App\Location;
use Illuminate\Database\Seeder;

class LocationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Location::insert([
            ["id"=>1,"place_id"=>1,"address"=>"Heinrichstrasse 89"],
            ["id"=>2,"place_id"=>1,"address"=>"Heinrichstrasse 92"],
            ["id"=>3,"place_id"=>1,"address"=>"Ottostrasse 35"],
            ["id"=>4,"place_id"=>1,"address"=>"Barbarossahof 4/5"],
            ["id"=>5,"place_id"=>1,"address"=>"Barbarossahof 2"],
            ["id"=>6,"place_id"=>1,"address"=>"Barbarossahof 18"],
            ["id"=>7,"place_id"=>1,"address"=>"Barbarossahof 1"],
            ["id"=>8,"place_id"=>2,"address"=>"Puschkinstrasse 1"],
            ["id"=>9,"place_id"=>2,"address"=>"Blücherstrasse 6"],
            ["id"=>10,"place_id"=>3,"address"=>"Landsberger Strasse 23"],
            ["id"=>11,"place_id"=>3,"address"=>"Landsberger Strasse 4"],
            ["id"=>12,"place_id"=>3,"address"=>"Franz-Mehring-Strasse 3"],
            ["id"=>13,"place_id"=>4,"address"=>"Löscherstrasse 16"],
            ["id"=>14,"place_id"=>4,"address"=>"Mendelssohnallee 8"],
            ["id"=>15,"place_id"=>4,"address"=>"Glashütter Strasse 101"],
            ["id"=>16,"place_id"=>4,"address"=>"Glashütter Strasse 101A"],
            ["id"=>17,"place_id"=>5,"address"=>"Parkstrasse 28"],
            ["id"=>18,"place_id"=>5,"address"=>"Barbarossastrasse 2"],
            ["id"=>19,"place_id"=>6,"address"=>"Trachenbergring 93"]
        ]);
    }
}
