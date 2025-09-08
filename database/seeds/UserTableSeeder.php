<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\User;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::insert([
            ["id"=>1,"name"=>"admin","email"=>"admin@miqr.com","password"=>Hash::make('password')],

        ]);
    }

}
