<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Entfernen" on the Dashboard's Kündigungen box: an entry that is no longer
 * needed (Kündigung zurückgezogen, Vertrag verlängert, ...) leaves the list
 * like a delete (soft delete), but keeps WHY, WHO and WHEN, so the Verlauf can
 * tell it apart from a plain "Löschen". All nullable - existing rows untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('terminations', function (Blueprint $table) {
            $table->string('removal_reason')->nullable()->after('is_active');
            $table->text('removal_note')->nullable()->after('removal_reason');
            $table->unsignedBigInteger('removed_by')->nullable()->after('removal_note');
            $table->dateTime('removed_at')->nullable()->after('removed_by');
        });
    }

    public function down(): void
    {
        Schema::table('terminations', function (Blueprint $table) {
            $table->dropColumn(['removal_reason', 'removal_note', 'removed_by', 'removed_at']);
        });
    }
};
