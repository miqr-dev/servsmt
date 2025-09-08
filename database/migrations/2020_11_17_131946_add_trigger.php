<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    // public function up()
    // {
    //     DB::unprepared('Create Trigger inv_ab_items_after_insert AFTER INSERT ON  `inv_ab_items` FOR EACH ROW
    //     BEGIN
    //     INSERT INTO inv_items SET dateupd = NEW.andat, gart_id = NEW.gart_id, gname = NEW.gname, gtyp = NEW.gtyp, invnr = NEW.invnr, sn = NEW.sn;
    //     END');
    // }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
