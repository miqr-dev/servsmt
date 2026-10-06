<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What each imported OU is (2026-10-05), filled by ad:import-inventory:
 *   Standort Dresden > Computer > Löscherstr. -16 (DD1) > 3.OG > 3.05 |4115 > Teilnehmer-TCs
 *   standort           container  adresse                 etage  raum         gruppe
 * and where each computer is (room / group / Standort).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ad_ous', function (Blueprint $table) {
            $table->string('type', 20)->nullable()->index()->after('name'); // standort|container|adresse|etage|raum|gruppe|other
            $table->string('label')->nullable()->after('type');             // display name: "Dresden", "3.05", "Teilnehmer"
            $table->foreignId('standort_ou_id')->nullable()->index()->after('parent_id');
        });

        Schema::table('ad_computers', function (Blueprint $table) {
            $table->foreignId('room_ou_id')->nullable()->index()->after('ad_ou_id');     // the Raum OU (also when in Teilnehmer-/Dozenten-TCs)
            $table->foreignId('standort_ou_id')->nullable()->index()->after('room_ou_id');
            $table->string('group', 20)->nullable()->after('standort_ou_id');           // Teilnehmer | Dozent | null
        });
    }

    public function down(): void
    {
        Schema::table('ad_computers', function (Blueprint $table) {
            $table->dropColumn(['room_ou_id', 'standort_ou_id', 'group']);
        });
        Schema::table('ad_ous', function (Blueprint $table) {
            $table->dropColumn(['type', 'label', 'standort_ou_id']);
        });
    }
};
