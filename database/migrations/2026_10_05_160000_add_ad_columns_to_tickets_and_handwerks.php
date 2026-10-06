<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Step 2 of switching to AD rooms/computers (2026-10-05): AD columns next to
 * the old ones. Only new nullable columns - the old room_id / gname_id /
 * inv_item_id stay and keep working. Old tickets are filled from the links
 * of step 1 (App\Support\AdInventoryLinker::backfillTickets).
 * (korsos has no room column - nothing to do there.)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('ad_room_id')->nullable()->index();      // ad_ous (Raum)
            $table->foreignId('ad_computer_id')->nullable()->index();  // ad_computers
        });
        Schema::table('ticket_pcs', function (Blueprint $table) {
            $table->foreignId('ad_computer_id')->nullable()->index();
        });
        Schema::table('handwerks', function (Blueprint $table) {
            $table->foreignId('ad_room_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('handwerks', fn (Blueprint $t) => $t->dropColumn('ad_room_id'));
        Schema::table('ticket_pcs', fn (Blueprint $t) => $t->dropColumn('ad_computer_id'));
        Schema::table('tickets', fn (Blueprint $t) => $t->dropColumn(['ad_room_id', 'ad_computer_id']));
    }
};
