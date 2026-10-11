<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Devices in the AD / app rooms that are not in AD and not in the old
 * inventory (2026-10-09): added by users in the ticket forms when the device
 * is not in the room's list ("Gerät hinzufügen"), or by IT on Einstellungen >
 * Standorte & Adressen. IT confirms user entries there (checked_at).
 * Nothing is written to AD.
 *
 * tickets.ad_device_id     - the printer / phone / projector the ticket is about
 * tickets.ad_pc_device_id  - a PC that is not in AD (described by the user)
 * ticket_pcs.ad_device_id  - such PCs on "PC Standort ändern"
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ad_devices')) {
            Schema::create('ad_devices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ad_ou_id')->index();          // room (ad_ous, AD or app)
                $table->string('kind', 20);                      // rechner | drucker | scanner | telefon | beamer | sonstiges
                $table->string('name', 100);
                $table->string('invnr', 50)->nullable();         // Inventar-Nr. / sticker, if known
                $table->string('note')->nullable();
                $table->foreignId('created_by')->nullable();
                $table->timestamp('checked_at')->nullable();     // confirmed by IT
                $table->foreignId('checked_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        Schema::table('tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('tickets', 'ad_device_id')) {
                $table->foreignId('ad_device_id')->nullable()->index();
            }
            if (! Schema::hasColumn('tickets', 'ad_pc_device_id')) {
                $table->foreignId('ad_pc_device_id')->nullable()->index();
            }
        });
        Schema::table('ticket_pcs', function (Blueprint $table) {
            if (! Schema::hasColumn('ticket_pcs', 'ad_device_id')) {
                $table->foreignId('ad_device_id')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ticket_pcs', function (Blueprint $t) {
            if (Schema::hasColumn('ticket_pcs', 'ad_device_id')) {
                $t->dropIndex(['ad_device_id']);
                $t->dropColumn('ad_device_id');
            }
        });
        Schema::table('tickets', function (Blueprint $t) {
            foreach (['ad_device_id', 'ad_pc_device_id'] as $col) {
                if (Schema::hasColumn('tickets', $col)) {
                    $t->dropIndex([$col]);
                    $t->dropColumn($col);
                }
            }
        });
        Schema::dropIfExists('ad_devices');
    }
};
