<?php

use Illuminate\Database\Seeder;
use App\Permission;
use App\Permissioncategory;

/**
 * Permissions the code actually checks (2026-09-28) - all of them Inventory,
 * via @can / ->can() in resources/views/inventory/index.blade.php.
 *
 * Everything else in the app is controlled by ROLES, not permissions:
 * config/route_access.php (per-URL role rules), controller checks and
 * App\User::hasRole (Super_Admin = every role). The earlier taxonomy
 * (tickets-*, korso-*, handwerk-*, teilnehmer-*, personal-*, role-*,
 * permission-*, …) was never checked anywhere and has been removed from the
 * code. Rows already in the permissions table are left untouched (no data
 * deleted) - they just aren't used.
 *
 * Names are exactly as the views check them. Note: the view checks
 * 'umbenennen' (rename) - the original seeded name was 'Umbennen', which
 * nothing checks. 'Fehlende_inv' was checked but never seeded before.
 *
 * Idempotent - safe to re-run (firstOrCreate on name+guard_name).
 */
class PermissionTableSeeder extends Seeder
{
    public function run()
    {
        $byCategory = [
            'Geräte' => ['Aktuell', 'Ausgemustert', 'Ändern', 'Bewegen', 'Ausmustern', 'umbenennen', 'information'],
            'Erfassen' => ['Erfassen_Auto', 'Erfassen_Manuell', 'Inventur'],
            'Drucken' => ['Drucken_list', 'Drucken_ticket', 'Fehlende_inv'],
        ];

        foreach ($byCategory as $categoryName => $permissionNames) {
            $category = Permissioncategory::where('name', $categoryName)->first();

            if (! $category) {
                throw new \RuntimeException("Permissioncategory '{$categoryName}' not found - run PermissioncategoryTableSeeder first.");
            }

            foreach ($permissionNames as $permissionName) {
                Permission::firstOrCreate(
                    ['name' => $permissionName, 'guard_name' => 'web'],
                    ['permissioncategory_id' => $category->id]
                );
            }
        }
    }
}
