<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParticipantTicketTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('participant_ticket_tables', function (Blueprint $table) {
            $table->id();
            $table->integer('ticket_id');
            $table->string('location')->nullable();
            $table->string('vorname');
            $table->string('nachname');
            $table->string('course');
            $table->string('email')->nullable();
            $table->string('notes_participant')->nullable();
            $table->string('password')->nullable();
            $table->string('username')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('participant_ticket_tables');
    }
}
