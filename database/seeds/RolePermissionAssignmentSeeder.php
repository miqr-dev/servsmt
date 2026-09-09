<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Permission;

/**
 * Default permission -> role assignments for the taxonomy seeded by
 * PermissionTableSeeder, matching servsmt_permission_taxonomy_proposal.md's
 * "suggested default" column. Deliberately conservative wherever the
 * proposal flagged uncertainty (Personal, Matrix, Content/Aufgaben/Lizenzen):
 * Super_Admin only for now, easy to widen later once confirmed - never the
 * other way around.
 *
 * This changes nothing observable by itself: no controller checks any of
 * these permissions yet, so this only populates data the roles admin UI
 * will display. Must run after RoleTableSeeder + PermissionTableSeeder.
 * Idempotent (syncPermissions replaces the set, safe to re-run).
 */
class RolePermissionAssignmentSeeder extends Seeder
{
    public function run()
    {
        $assignments = [
            // Super_Admin gets every permission explicitly (belt-and-suspenders
            // alongside the Gate::before bypass in AuthServiceProvider - this
            // also means the roles.edit screen shows Super_Admin's checkboxes
            // as fully checked instead of misleadingly empty).
            'Super_Admin' => '*',

            'HR' => [
                'personal-view', 'personal-manage', 'termination-manage',
            ],

            'Korso_Admin' => [
                'korso-view', 'korso-create', 'korso-assign', 'korso-manage', 'korso-delete',
                'korso-marketing-manage',
            ],

            'Korso_ma' => [
                'korso-view', 'korso-create',
            ],

            'handwerk_admin' => [
                'handwerk-view', 'handwerk-create', 'handwerk-assign', 'handwerk-manage', 'handwerk-delete',
            ],

            'Verwaltung' => [
                'tickets-create', 'tickets-view',
                'handwerk-view', 'handwerk-create',
                'teilnehmer-view', 'teilnehmer-manage', 'umfrage-manage',
            ],

            'Sekretariat' => [
                'tickets-create', 'tickets-view',
                'sekretariat-groups-manage',
            ],

            // Bewegen/Ausmustern/Umbennen (move/decommission/rename) held back
            // from INV for now, same "sensitive action -> Super_Admin only
            // initially" default used for inventar-*-delete/move before this
            // was renamed to the original permission names - easy to widen once
            // confirmed INV should have them day-to-day.
            'INV' => [
                'Aktuell', 'Ausgemustert', 'Ändern', 'information',
                'Erfassen_Auto', 'Erfassen_Manuell', 'Inventur',
                'Drucken_list', 'Drucken_ticket',
                'standort-view', 'standort-manage',
            ],

            'Teilnehmer_Info' => [
                'teilnehmer-view', 'teilnehmer-manage', 'umfrage-manage',
            ],
        ];

        $allPermissionNames = Permission::where('guard_name', 'web')->pluck('name')->all();

        foreach ($assignments as $roleName => $permissionNames) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();

            if (! $role) {
                throw new \RuntimeException("Role '{$roleName}' not found - run RoleTableSeeder first.");
            }

            $names = $permissionNames === '*' ? $allPermissionNames : $permissionNames;
            $role->syncPermissions($names);
        }
    }
}
