<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Role "Korso_verwaltung" (2026-10-02): notified when a submitter restores a
 * Korso ticket nobody is assigned to. Was hardcoded user 39 in
 * KorsoController@restore; 39 gets the role so nothing changes on day 1.
 * Notification only - no rights. Managed under Rollen & Berechtigungen > Korso.
 */
return new class extends Migration
{
    public function up(): void
    {
        Role::findOrCreate('Korso_verwaltung', 'web');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        \App\User::withTrashed()->find(39)?->assignRole('Korso_verwaltung');
    }

    public function down(): void
    {
        // keep the role and its assignments
    }
};
