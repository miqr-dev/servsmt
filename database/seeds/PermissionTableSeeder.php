<?php

use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::insert([
          ["id"=>1,"name"=>"Aktuell","guard_name"=>"web","category_id"=>1],
          ["id"=>2,"name"=>"Ausgemustert","guard_name"=>"web","category_id"=>1],
          ["id"=>3,"name"=>"Ändern","guard_name"=>"web","category_id"=>null],
          ["id"=>4,"name"=>"Erfassen_Auto","guard_name"=>"web","category_id"=>2],
          ["id"=>5,"name"=>"Erfassen_Manuell","guard_name"=>"web","category_id"=>2],
          ["id"=>6,"name"=>"Bewegen","guard_name"=>"web","category_id"=>null],
          ["id"=>7,"name"=>"Ausmustern","guard_name"=>"web","category_id"=>null],
          ["id"=>8,"name"=>"Drucken_list","guard_name"=>"web","category_id"=>3],
          ["id"=>9,"name"=>"Drucken_ticket","guard_name"=>"web","category_id"=>3],
          ["id"=>10,"name"=>"Inventur","guard_name"=>"web","category_id"=>null],
          ["id"=>11,"name"=>"role-list","guard_name"=>"web","category_id"=>5],
          ["id"=>12,"name"=>"role-create","guard_name"=>"web","category_id"=>5],
          ["id"=>13,"name"=>"role-edit","guard_name"=>"web","category_id"=>5],
          ["id"=>14,"name"=>"role-delete","guard_name"=>"web","category_id"=>5],
          ["id"=>15,"name"=>"information","guard_name"=>"web","category_id"=>null],
          ["id"=>16,"name"=>"Umbennen","guard_name"=>"web","category_id"=>null],
          ["id"=>17,"name"=>"Ticket_IT","guard_name"=>"web","category_id"=>7],
          ["id"=>18,"name"=>"Ticket_Erstellen","guard_name"=>"web","category_id"=>7],
          ["id"=>19,"name"=>"Meine_Tickets","guard_name"=>"web","category_id"=>7],
        ]);
    }
}
