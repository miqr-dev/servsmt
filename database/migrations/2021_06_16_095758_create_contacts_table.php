<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(); //last name
            $table->string('vorname')->nullable();
            $table->string('anrede')->nullable();
            $table->string('username')->unique(); 
            $table->string('position')->nullable(); 
            $table->string('beschreibung')->nullable(); 
            $table->string('abteilung')->nullable(); 
            $table->string('office')->nullable(); 
            $table->string('abschluss')->nullable(); 
            $table->string('plz')->nullable(); 
            $table->string('bundesland')->nullable(); 
            $table->string('straße')->nullable(); 
            $table->string('ort')->nullable(); 
            $table->string('tel')->nullable(); 
            $table->string('privat')->nullable(); 
            $table->string('mobil')->nullable(); 
            $table->string('email')->unique();
            $table->string('guid')->unique()->nullable();
            $table->string('domain')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contacts');
    }
}
