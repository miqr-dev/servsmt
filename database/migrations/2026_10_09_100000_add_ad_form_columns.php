<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AD switch step 4 (2026-10-09): the new ticket forms pick rooms / PCs from AD.
 *
 * - tickets.ad_target_room_id: AD room a PC / printer / phone moves to
 *   (next to the old tel_target_place / tel_target_room).
 * - tickets.ad_picked, handwerks.ad_picked: 1 = the AD room / PC was picked
 *   in the form. AdInventoryLinker::backfillTickets() skips these rows, so a
 *   later change of an old-inventory link never overwrites what was picked.
 * - ticket_pcs.inv_item_id becomes nullable: "PC Standort ändern" can list
 *   AD PCs that have no old inventory item. The foreign key is dropped and
 *   re-added around the change (MySQL and MariaDB both accept that).
 *
 * Safe to re-run on a fresh copy of the production DB: every step checks
 * first and is skipped when already done.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('tickets', 'ad_target_room_id')) {
                $table->foreignId('ad_target_room_id')->nullable()->index(); // ad_ous (Raum)
            }
            if (! Schema::hasColumn('tickets', 'ad_picked')) {
                $table->boolean('ad_picked')->default(false);
            }
        });

        Schema::table('handwerks', function (Blueprint $table) {
            if (! Schema::hasColumn('handwerks', 'ad_picked')) {
                $table->boolean('ad_picked')->default(false);
            }
        });

        $column = collect(Schema::getColumns('ticket_pcs'))->firstWhere('name', 'inv_item_id');
        if ($column && ! $column['nullable']) {
            $fk = collect(Schema::getForeignKeys('ticket_pcs'))
                ->first(fn ($k) => $k['columns'] === ['inv_item_id']);
            if ($fk) {
                Schema::table('ticket_pcs', fn (Blueprint $t) => $t->dropForeign($fk['name']));
            }
            Schema::table('ticket_pcs', function (Blueprint $t) {
                $t->unsignedBigInteger('inv_item_id')->nullable()->change();
            });
            Schema::table('ticket_pcs', function (Blueprint $t) {
                $t->foreign('inv_item_id')->references('id')->on('inv_items')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        // ticket_pcs.inv_item_id stays nullable: rows without an old item may exist by then.
        Schema::table('handwerks', function (Blueprint $t) {
            if (Schema::hasColumn('handwerks', 'ad_picked')) {
                $t->dropColumn('ad_picked');
            }
        });
        Schema::table('tickets', function (Blueprint $t) {
            if (Schema::hasColumn('tickets', 'ad_picked')) {
                $t->dropColumn('ad_picked');
            }
            if (Schema::hasColumn('tickets', 'ad_target_room_id')) {
                $t->dropIndex(['ad_target_room_id']);
                $t->dropColumn('ad_target_room_id');
            }
        });
    }
};
