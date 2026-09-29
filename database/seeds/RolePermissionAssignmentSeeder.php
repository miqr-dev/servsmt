<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Permission;

/**
 * Default permission -> role assignments (2026-09-28: Inventory only).
 *
 * Only Inventory is permission-based; every other area is controlled by
 * roles (config/route_access.php). Super_Admin passes every permission via
 * Gate::before anyway - it's also given every permission here so the roles
 * edit screen shows its checkboxes as fully checked.
 *
 * Bewegen / Ausmustern / umbenennen (move / decommission / rename) are held
 * back from INV - "sensitive action -> Super_Admin only" until confirmed.
 *
 * Must run after RoleTableSeeder + PermissionTableSeeder. Idempotent
 * (syncPermissions replaces the set). Only the roles listed here are
 * touched; other roles' existing permission rows are left as they are.
 */
class RolePermissionAssignmentSeeder extends Seeder
{
    public function run()
    {
        $assignments = [
            'Super_Admin' => '*',

            'INV' => [
                'Aktuell', 'Ausgemustert', 'Ändern', 'information',
                'Erfassen_Auto', 'Erfassen_Manuell', 'Inventur',
                'Drucken_list', 'Drucken_ticket',
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
