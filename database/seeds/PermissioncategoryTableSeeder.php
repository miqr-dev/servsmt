<?php

use Illuminate\Database\Seeder;
use App\Permissioncategory;

/**
 * Permission categories used to group checkboxes in roles.create / roles.edit.
 *
 * 2026-09-28: trimmed to the categories whose permissions the code actually
 * checks - Inventory only (Geräte / Erfassen / Drucken). Everything else is
 * controlled by ROLES (config/route_access.php + App\User::hasRole). Old
 * category rows already in the DB are left untouched (no data deleted).
 *
 * Idempotent - safe to re-run.
 */
class PermissioncategoryTableSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'Geräte',
            'Erfassen',
            'Drucken',
        ];

        foreach ($categories as $name) {
            Permissioncategory::firstOrCreate(['name' => $name]);
        }
    }
}
