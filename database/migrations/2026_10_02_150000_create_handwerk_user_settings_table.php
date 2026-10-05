<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Handwerk-Zuständigkeiten (2026-10-02) - replaces the hardcoded user ids in
 * the Handwerk code (managed under Rollen & Berechtigungen > Handwerk):
 *
 * - role Handwerk_verwaltung: notified about new / restored Handwerk tickets,
 *   comments on unassigned tickets and Erledigt by Sekretariat (was user 327).
 * - handwerk_user_settings, one row per user:
 *   view_cities   cities whose Handwerk tickets the user sees in Meine Tickets
 *                 (was $userCities: 1, 63, 327 in TicketController@usertickets)
 *   assign_cities the user appears in "Zuweisen" on tickets of these cities
 *                 (was 14441 for Leipzig in Handwerk/Show.vue)
 *   pdf_by_mail   assignment e-mail gets the ticket PDF attached
 *                 (was 1, 1473, 14441, 23192 in HandwerkController@assignedTo)
 *
 * The current hardcoded behaviour is copied in, so nothing changes on day 1.
 * New table + new role only; nothing existing is deleted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('handwerk_user_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->json('view_cities')->nullable();
            $table->json('assign_cities')->nullable();
            $table->boolean('pdf_by_mail')->default(false);
            $table->timestamps();
        });

        Role::findOrCreate('Handwerk_verwaltung', 'web');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $exists = fn (int $id) => DB::table('users')->where('id', $id)->exists();

        // --- copy the hardcoded values ---
        if ($exists(327)) {
            \App\User::withTrashed()->find(327)?->assignRole('Handwerk_verwaltung');
        }

        // city names as stored on the tickets ("Erfurt", "Döbeln", ...)
        $known = DB::table('handwerks')->whereNotNull('submitter_standort')->distinct()->pluck('submitter_standort')
            ->map(fn ($c) => trim($c))->filter()->unique()->values();
        $name = function (string $old) use ($known) {
            $old = $old === 'chemntiz' ? 'chemnitz' : $old; // typo in the old array
            $match = $known->first(fn ($k) => mb_strtolower($k, 'UTF-8') === $old);

            return $match ?: mb_convert_case($old, MB_CASE_TITLE, 'UTF-8');
        };

        $settings = [];
        foreach ([
            1 => ['erfurt', 'dresden', 'berlin', 'leipzig', 'chemntiz', 'döbeln'],
            63 => ['dresden', 'berlin', 'leipzig', 'chemntiz', 'döbeln'],
            327 => ['dresden', 'berlin', 'leipzig', 'chemntiz', 'döbeln', 'erfurt', 'suhl'],
        ] as $id => $cities) {
            $settings[$id]['view_cities'] = array_values(array_unique(array_map($name, $cities)));
        }
        $settings[14441]['assign_cities'] = [$name('leipzig')];
        foreach ([1, 1473, 14441, 23192] as $id) {
            $settings[$id]['pdf_by_mail'] = true;
        }

        foreach ($settings as $id => $s) {
            if (! $exists($id)) {
                continue;
            }
            DB::table('handwerk_user_settings')->insert([
                'user_id' => $id,
                'view_cities' => isset($s['view_cities']) ? json_encode($s['view_cities'], JSON_UNESCAPED_UNICODE) : null,
                'assign_cities' => isset($s['assign_cities']) ? json_encode($s['assign_cities'], JSON_UNESCAPED_UNICODE) : null,
                'pdf_by_mail' => $s['pdf_by_mail'] ?? false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('handwerk_user_settings');
    }
};
