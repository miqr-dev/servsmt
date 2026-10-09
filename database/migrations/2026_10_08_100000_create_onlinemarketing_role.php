<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Role "Onlinemarketing" (2026-10-08): notified about every new Korso
 * ticket of type Onlinemarketing. Was hardcoded user 163 in
 * KorsoController@store; 163 gets the role so nothing changes on day 1.
 * Notification only - no rights. Managed under Rollen & Berechtigungen > Korso.
 */
return new class extends Migration
{
    public function up(): void
    {
        Role::findOrCreate('Onlinemarketing', 'web');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        \App\User::withTrashed()->find(163)?->assignRole('Onlinemarketing');
    }

    public function down(): void
    {
        // keep the role and its assignments
    }
};
