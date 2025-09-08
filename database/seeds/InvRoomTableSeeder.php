<?php

use App\InvRoom;
use Illuminate\Database\Seeder;

class InvRoomTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        InvRoom::insert([
            //TODO: Real Rooms Chemnitz Parkstrasse 28
            ["id"=> 5001,"place_id"=> 5,"location_id"=> 17,"etage"=> "EG","rname"=> 0.01,"altrname"=> "IT"],
            ["id"=> 5002,"place_id"=> 5,"location_id"=> 17,"etage"=> "EG","rname"=> 0.02,"altrname"=> "KBM 7"],
            ["id"=> 5003,"place_id"=> 5,"location_id"=> 17,"etage"=> "EG","rname"=> 0.03,"altrname"=> "Dozenten"],
            ["id"=> 5004,"place_id"=> 5,"location_id"=> 17,"etage"=> "EG","rname"=> 0.04,"altrname"=> "APO/FOSI"],
            ["id"=> 5005,"place_id"=> 5,"location_id"=> 17,"etage"=> "EG","rname"=> 0.05,"altrname"=> ""],
            ["id"=> 5006,"place_id"=> 5,"location_id"=> 17,"etage"=> "EG","rname"=> 0.12,"altrname"=> "KBM 6"],
            ["id"=> 5007,"place_id"=> 5,"location_id"=> 17,"etage"=> "EG","rname"=> 0.13,"altrname"=> "E-COM"],
            ["id"=> 5008,"place_id"=> 5,"location_id"=> 17,"etage"=> "EG","rname"=> 0.14,"altrname"=> "KBM 8 "],
            ["id"=> 5009,"place_id"=> 5,"location_id"=> 17,"etage"=> "5","rname"=> 5.03,"altrname"=> "OSI / IBO"],
            ["id"=> 5010,"place_id"=> 5,"location_id"=> 17,"etage"=> "5","rname"=> 5.04,"altrname"=> "Sekretariat"],
            ["id"=> 5011,"place_id"=> 5,"location_id"=> 17,"etage"=> "5","rname"=> 5.05,"altrname"=> ""],
            ["id"=> 5012,"place_id"=> 5,"location_id"=> 17,"etage"=> "5","rname"=> 5.06,"altrname"=> "Büro Lorenz"],
            ["id"=> 5013,"place_id"=> 5,"location_id"=> 17,"etage"=> "5","rname"=> 5.08,"altrname"=> "Serverraum"],
            ["id"=> 5014,"place_id"=> 5,"location_id"=> 17,"etage"=> "5","rname"=> 5.10,"altrname"=> "BPW"],
            ["id"=> 5015,"place_id"=> 5,"location_id"=> 17,"etage"=> "5","rname"=> 5.11,"altrname"=> "KBM 9"],
            // TODO: Real Rooms Erfurt Heinriech 89
            ["id"=> 1001,"place_id"=> 1,"location_id"=> 1,"etage"=> 1,"rname"=> 1.01,"altrname"=> "Leitung Arbeitspsychologischer Dienst"],
            ["id"=> 1002,"place_id"=> 1,"location_id"=> 1,"etage"=> 1,"rname"=> 1.02,"altrname"=> "Computer- und Schulungsraum"],
            ["id"=> 1003,"place_id"=> 1,"location_id"=> 1,"etage"=> 1,"rname"=> 1.06,"altrname"=> "Sportraum"],
            ["id"=> 1004,"place_id"=> 1,"location_id"=> 1,"etage"=> 1,"rname"=> 1.07,"altrname"=> "Schulungsraum neben Sportrau"],
            ["id"=> 1005,"place_id"=> 1,"location_id"=> 1,"etage"=> 1,"rname"=> 1.08,"altrname"=> "Arbeitspädagogischer Dienst"],
            ["id"=> 1006,"place_id"=> 1,"location_id"=> 1,"etage"=> 1,"rname"=> 1.09,"altrname"=> "Zu geschlossen"],
            ["id"=> 1007,"place_id"=> 1,"location_id"=> 1,"etage"=> 1,"rname"=> 1.10,"altrname"=> "Schulungsraum neben Sitzecke"],
            ["id"=> 1008,"place_id"=> 1,"location_id"=> 1,"etage"=> 1,"rname"=> 1.11,"altrname"=> "Schulungsraum Fenster"],
            ["id"=> 1009,"place_id"=> 1,"location_id"=> 1,"etage"=> 3,"rname"=> 3.05,"altrname"=> "Raum Doc. Staffel"],
            ["id"=> 1010,"place_id"=> 1,"location_id"=> 1,"etage"=> 3,"rname"=> 3.06,"altrname"=> "Assistenz Bee"],
            ["id"=> 1011,"place_id"=> 1,"location_id"=> 1,"etage"=> 3,"rname"=> 3.07,"altrname"=> "Kors"],
            ["id"=> 1012,"place_id"=> 1,"location_id"=> 1,"etage"=> 3,"rname"=> 3.08,"altrname"=> "Schulungsraum"],
            ["id"=> 1013,"place_id"=> 1,"location_id"=> 1,"etage"=> 3,"rname"=> 3.09,"altrname"=> "Sekreteriat"],
            ["id"=> 1014,"place_id"=> 1,"location_id"=> 1,"etage"=> 3,"rname"=> 3.11,"altrname"=> "Beratungsraum D.Staffe"],
            ["id"=> 1015,"place_id"=> 1,"location_id"=> 1,"etage"=> 3,"rname"=> 3.12,"altrname"=> "Kaufmännischer Dienst"]
        ]);
    }
}
