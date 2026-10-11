<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Small key/value table for settings changed in the app (2026-10-11).
 *
 * First entry: the default Windows password for new employees, mailed with
 * "Neuer Mitarbeiter" (Erledigt 2). It was hardcoded in TicketNotification;
 * now Einstellungen > Neuer Mitarbeiter, with a reminder every 1 January
 * (App\Support\EmployeePassword). The current value is copied over, so the
 * mail doesn't change. Additive; safe to run again.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('app_settings')) {
            Schema::create('app_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key', 100)->unique();
                $table->text('value')->nullable();
                $table->foreignId('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! DB::table('app_settings')->where('key', 'new_employee_password')->exists()) {
            DB::table('app_settings')->insert([
                'key' => 'new_employee_password',
                'value' => 'Miqr.2026#',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
