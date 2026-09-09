<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stage 1 of the inv_items / inv_ab_items merge.
 *
 * Purely additive: adds every column that today only exists on
 * inv_ab_items onto inv_items, so a single row can eventually hold
 * everything about an inventory item. Nothing is copied, nothing is
 * deleted, no existing column is touched - safe to run at any time,
 * old code keeps working exactly as it does today because it never
 * looks at these new columns.
 */
class AddAbItemColumnsToInvItemsTable extends Migration
{
    public function up()
    {
        Schema::table('inv_items', function (Blueprint $table) {
            $table->date('andat')->nullable()->after('dateupd');
            $table->foreignId('location_id')->nullable()->after('room_id');
            $table->float('kp', 8, 2)->nullable()->after('gtyp');
            $table->mediumText('notes')->nullable()->after('kp');
            $table->string('path_to_rg', 50)->nullable()->after('notes');
            $table->date('ausdat')->nullable()->after('path_to_rg');
            $table->foreignId('amg_id')->nullable()->after('ausdat');

            $table->foreign('location_id')->references('id')->on('locations');
            $table->foreign('amg_id')->references('id')->on('amgs');
        });
    }

    public function down()
    {
        Schema::table('inv_items', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropForeign(['amg_id']);
            $table->dropColumn(['andat', 'location_id', 'kp', 'notes', 'path_to_rg', 'ausdat', 'amg_id']);
        });
    }
}
