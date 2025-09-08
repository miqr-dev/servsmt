<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('tickets', function (Blueprint $table) {
        $table->id();
        $table->integer('submitter');
        $table->tinyInteger('priority_id')->default(2);
        $table->string('tel_number');
        $table->string('custom_tel_number')->nullable();
        $table->string('problem_type');
        $table->integer('gname_id')->nullable();
        $table->integer('gart_id')->nullable();
        $table->string('searchsoftware')->nullable();
        $table->string('software_name')->nullable();
        $table->string('software_reason')->nullable();
        $table->text('notizen')->nullable();
        $table->string('keyboard')->nullable();
        $table->string('mouse')->nullable();
        $table->string('speaker')->nullable();
        $table->string('headset')->nullable();
        $table->string('webcam')->nullable();
        $table->string('monitor')->nullable();
        $table->string('other')->nullable();
        $table->string('geht_nicht_an')->nullable();
        $table->string('blue')->nullable();
        $table->string('black')->nullable();
        $table->string('slow_computer')->nullable();
        $table->string('web_cam_problem')->nullable();
        $table->string('head_set_problem')->nullable();
        $table->string('lautsprecher_mal')->nullable();
        $table->string('keyboard_malfunction')->nullable();
        $table->string('mouse_mal')->nullable();
        $table->string('slow_network')->nullable();
        $table->string('no_network_drive')->nullable();
        $table->string('laud_fan')->nullable();
        $table->string('scanner_wrong_folder')->nullable();
        $table->string('scanner_not_working')->nullable();
        $table->string('scanner_myname_list')->nullable();
        $table->string('pc_laptop_others')->nullable();
        $table->integer('location_id')->nullable();
        $table->integer('room_id')->nullable();
        $table->string('printer_name')->nullable();
        $table->string('assignedTo')->nullable();
        $table->integer('ticket_status_id')->default(1);
        $table->integer('replication_id')->nullable();
        $table->string('position_employee')->nullable();
        $table->string('abteilung_employee')->nullable();
        $table->string('telephone_employee')->nullable();
        $table->string('outlook')->nullable();
        $table->string('isplus')->nullable();
        $table->string('employee_firstname')->nullable();
        $table->string('employee_lastname')->nullable();
        $table->string('inaktiv')->nullable();
        $table->string('other_error_participant')->nullable();
        $table->string('forgotten')->nullable();
        $table->string('abgelaufen')->nullable();
        $table->string('password_name')->nullable();
        $table->string('expiring_date')->nullable();
        $table->string('user_oldname')->nullable();
        $table->string('user_newname')->nullable();
        $table->string('user_other_username')->nullable();
        $table->integer('tel_target_place')->nullable();
        $table->integer('tel_target_room')->nullable();
        $table->string('current_tel_name')->nullable();
        $table->string('new_tel_name')->nullable();
        $table->string('new_tel_number')->nullable();
        $table->string('terminal_name')->nullable();
        $table->string('terminal_datev')->nullable();
        $table->string('terminal_lexware')->nullable();
        $table->string('bbb_subject')->nullable();
        $table->string('bbb_username')->nullable();
        $table->string('vtiger_subject')->nullable();
        $table->string('vtiger_username')->nullable();
        $table->string('smt_subject')->nullable();
        $table->string('smt_username')->nullable();
        $table->string('firmen_subject')->nullable();
        $table->string('firmen_username')->nullable();
        $table->string('participant_location')->nullable();
        $table->dateTime('participant_required_at')->nullable();
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
        Schema::dropIfExists('tickets');
    }
}
