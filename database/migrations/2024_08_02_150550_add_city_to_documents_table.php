<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCityToDocumentsTable extends Migration
{
   public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('city')->after('bundesland')->nullable();  // Adding the city column
        });
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('city');  // Dropping the city column if rolled back
        });
    }
}
