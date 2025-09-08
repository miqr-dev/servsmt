<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHandwerksTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('handwerks', function (Blueprint $table) {
      $table->id();
      $table->string('submitter_name')->nullable();
      $table->integer('submitter')->nullable();
      $table->string('submitter_standort')->nullable();
      $table->string('submitter_adresse')->nullable();
      $table->tinyInteger('priority')->default(2);
      $table->integer('ticket_status_id')->default(1);
      $table->string('tel_number')->nullable();
      $table->string('problem_type');
      $table->string('assignedTo')->nullable();
      $table->string('done_by')->nullable();
      $table->integer('location_id')->nullable();
      $table->integer('room_id')->nullable();
      $table->string('schiebetafel')->nullable();       // Mobiliar
      $table->string('whiteboard')->nullable();
      $table->string('kreidetafel')->nullable();
      $table->string('schreibtisch_TN_70x70')->nullable();
      $table->string('schreibtisch_TN_80x80')->nullable();
      $table->string('schreibtisch_TN_80x160')->nullable();
      $table->string('schreibtisch_DOZ_80x140')->nullable();
      $table->string('schreibtisch_DOZ_80x160')->nullable();
      $table->string('schreibtisch_DOZ_80x180')->nullable();
      $table->string('schreibtisch_MA_80x140')->nullable();
      $table->string('schreibtisch_MA_80x160')->nullable();
      $table->string('schreibtisch_MA_80x180')->nullable();
      $table->string('stehtisch')->nullable();
      $table->string('gesprächstisch_rund')->nullable();
      $table->string('konferenztisch')->nullable();
      $table->string('couchtisch')->nullable();
      $table->string('beistelltisch')->nullable();
      $table->string('schreibtischstuhl')->nullable();
      $table->string('bürostuhl')->nullable();
      $table->string('stapelstühl')->nullable();
      $table->string('rollcontainer')->nullable();
      $table->string('standcontainer')->nullable();
      $table->string('hochschrank')->nullable();
      $table->string('ordnerhöhen_2')->nullable();
      $table->string('ordnerhöhen_3')->nullable();
      $table->string('hängeschrank')->nullable();
      $table->string('lamellenvorhang')->nullable();
      $table->string('rollo')->nullable();
      $table->string('pinnwand')->nullable();
      $table->string('bilder')->nullable();
      $table->string('handtuchspender')->nullable();
      $table->string('toilettenpapierhalter')->nullable();
      $table->string('desinfektionsmittelspender')->nullable();
      $table->string('barzeile')->nullable();
      $table->string('bar_Hochstühle')->nullable();
      $table->string('küchenzeile')->nullable();
      $table->integer('schiebetafel_qty')->nullable();
      $table->integer('whiteboard_qty')->nullable();
      $table->integer('kreidetafel_qty')->nullable();
      $table->integer('schreibtisch_TN_70x70_qty')->nullable();
      $table->integer('schreibtisch_TN_80x80_qty')->nullable();
      $table->integer('schreibtisch_TN_80x160_qty')->nullable();
      $table->integer('schreibtisch_DOZ_80x140_qty')->nullable();
      $table->integer('schreibtisch_DOZ_80x160_qty')->nullable();
      $table->integer('schreibtisch_DOZ_80x180_qty')->nullable();
      $table->integer('schreibtisch_MA_80x140_qty')->nullable();
      $table->integer('schreibtisch_MA_80x160_qty')->nullable();
      $table->integer('schreibtisch_MA_80x180_qty')->nullable();
      $table->integer('stehtisch_qty')->nullable();
      $table->integer('gesprächstisch_rund_qty')->nullable();
      $table->integer('konferenztisch_qty')->nullable();
      $table->integer('couchtisch_qty')->nullable();
      $table->integer('beistelltisch_qty')->nullable();
      $table->integer('schreibtischstuhl_qty')->nullable();
      $table->integer('bürostuhl_qty')->nullable();
      $table->integer('stapelstühl_qty')->nullable();
      $table->integer('rollcontainer_qty')->nullable();
      $table->integer('standcontainer_qty')->nullable();
      $table->integer('hochschrank_qty')->nullable();
      $table->integer('ordnerhöhen_2_qty')->nullable();
      $table->integer('ordnerhöhen_3_qty')->nullable();
      $table->integer('hängeschrank_qty')->nullable();
      $table->integer('lamellenvorhang_qty')->nullable();
      $table->integer('rollo_qty')->nullable();
      $table->integer('pinnwand_qty')->nullable();
      $table->integer('bilder_qty')->nullable();
      $table->integer('handtuchspender_qty')->nullable();
      $table->integer('toilettenpapierhalter_qty')->nullable();
      $table->integer('desinfektionsmittelspender_qty')->nullable();
      $table->integer('barzeile_qty')->nullable();
      $table->integer('bar_Hochstühle_qty')->nullable();
      $table->integer('küchenzeile_qty')->nullable();
      $table->string('neustandort_room')->nullable();   // new standort
      $table->string('kühlschrank')->nullable();        // electro  
      $table->integer('kühlschrank_qty')->nullable();
      $table->string('ventilator')->nullable();
      $table->string('ventilator_qty')->nullable();
      $table->string('geschirrspüler')->nullable();
      $table->integer('geschirrspüler_qty')->nullable();
      $table->string('kaffeemaschine')->nullable();
      $table->integer('kaffeemaschine_qty')->nullable();
      $table->string('notizen')->nullable();
      $table->string('subject')->nullable();  //Reparatur
      $table->string('custom_room')->nullable();  //Reparatur
      $table->longText('admin_notes')->nullable();  //Reparatur
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
    Schema::dropIfExists('handwerks');
  }
}
