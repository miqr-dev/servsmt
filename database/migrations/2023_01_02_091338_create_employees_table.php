<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('empFirstName');
            $table->string('empLastName');
            $table->string('empUsername');
            $table->string('empEmail');
            $table->string('empPosition');
            $table->string('empAbteilung');
            $table->string('empTelefon');
            $table->string('empISplus');
            $table->string('location');
            $table->string('Ticketsubmitter');
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
        Schema::dropIfExists('employees');
    }
}
