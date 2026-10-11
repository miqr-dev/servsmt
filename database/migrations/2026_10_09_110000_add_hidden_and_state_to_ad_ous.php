<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Einstellungen > Standorte & Adressen (2026-10-09). App-only settings on the
 * AD OUs - nothing is ever written to AD, and the import never touches these:
 * - hidden: the OU (and everything below it) is left out of the ticket-form
 *   pickers, e.g. OUs that the import took for an address but aren't one
 *   ("NB-no-Energie" in Erfurt, "LAPTOPs" in Suhl). Old tickets still show it.
 * - state: Bundesland of a Standort, groups the Standort select. Empty = the
 *   default from config/ad_inventory.php ('states').
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ad_ous', function (Blueprint $table) {
            if (! Schema::hasColumn('ad_ous', 'hidden')) {
                $table->boolean('hidden')->default(false)->after('source');
            }
            if (! Schema::hasColumn('ad_ous', 'state')) {
                $table->string('state', 60)->nullable()->after('hidden');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ad_ous', function (Blueprint $table) {
            foreach (['state', 'hidden'] as $col) {
                if (Schema::hasColumn('ad_ous', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
