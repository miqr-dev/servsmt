<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rooms that are not in AD (Handwerk: kitchen, corridor, WC, ...) live in
 * ad_ous too, under an AD address or floor, so tickets have ONE room list
 * (2026-10-05). source = 'ad' (imported, the import owns them) | 'app'
 * (added on /ad-inventory, the import never touches them).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ad_ous', function (Blueprint $table) {
            $table->string('source', 10)->default('ad')->index()->after('guid');
            $table->foreignId('created_by')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('ad_ous', function (Blueprint $table) {
            $table->dropColumn(['source', 'created_by']);
        });
    }
};
