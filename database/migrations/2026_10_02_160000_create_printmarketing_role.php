<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Role "Printmarketing" = access to Printmarketing Verwaltung (2026-10-02).
 * Was hardcoded to users 1 and 312 in KorsoController / Korso/Dashboard.vue;
 * both get the role so nothing changes on day 1. Managed under
 * Rollen & Berechtigungen > Korso. New role only.
 */
return new class extends Migration
{
    public function up(): void
    {
        Role::findOrCreate('Printmarketing', 'web');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([1, 312] as $id) {
            \App\User::withTrashed()->find($id)?->assignRole('Printmarketing');
        }
    }

    public function down(): void
    {
        // keep the role and its assignments
    }
};
