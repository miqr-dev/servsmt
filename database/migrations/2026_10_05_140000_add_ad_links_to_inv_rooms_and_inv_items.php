<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Step 1 of switching to AD rooms/computers (2026-10-05): link the existing
 * inventory to the imported AD data. Only new nullable columns - nothing
 * existing is changed. Filled by App\Support\AdInventoryLinker (automatic)
 * and on /ad-inventory/zuordnung (manual).
 *   *_link = 'auto' | 'manual'  (manual links are never overwritten by the automatic run)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inv_rooms', function (Blueprint $table) {
            $table->foreignId('ad_ou_id')->nullable()->index();   // ad_ous (type raum)
            $table->string('ad_link', 10)->nullable();
        });
        Schema::table('inv_items', function (Blueprint $table) {
            $table->foreignId('ad_computer_id')->nullable()->index(); // ad_computers
            $table->string('ad_link', 10)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('inv_items', function (Blueprint $table) {
            $table->dropColumn(['ad_computer_id', 'ad_link']);
        });
        Schema::table('inv_rooms', function (Blueprint $table) {
            $table->dropColumn(['ad_ou_id', 'ad_link']);
        });
    }
};
