<?php

use Illuminate\Database\Seeder;
use App\Permission;
use App\Permissioncategory;

/**
 * Full permission taxonomy from servsmt_permission_taxonomy_proposal.md.
 * Seeding these does NOT change any current behaviour by itself - no
 * controller checks them yet. This only makes the roles admin UI able to
 * display and assign them. Enforcement is wired in per-controller later,
 * one category at a time, each verified separately.
 *
 * Geräte/Erfassen/Drucken use the ORIGINAL permission names from this same
 * file before it was rewritten (recovered via git diff after an accidental
 * overwrite) - they map precisely to real InvAbItemController actions and
 * are more authentic than anything invented here, so they're kept as-is
 * rather than replaced with a generic view/create/edit/delete/move set.
 *
 * Idempotent - safe to re-run (firstOrCreate on name+guard_name).
 */
class PermissionTableSeeder extends Seeder
{
    public function run()
    {
        $byCategory = [
            'Rollen' => ['role-list', 'role-create', 'role-edit', 'role-delete'],
            'Berechtigungen' => ['permission-list', 'permission-create', 'permission-edit', 'permission-delete'],
            'Benutzer' => ['user-list', 'user-create', 'user-edit', 'user-delete'],
            'Einstellungen' => ['settings-view', 'settings-edit'],

            'Ticket' => ['tickets-create', 'tickets-view', 'tickets-manage', 'tickets-delete', 'tickets-special'],

            'Korso' => ['korso-view', 'korso-create', 'korso-assign', 'korso-manage', 'korso-delete'],
            'Korso Marketing' => ['korso-marketing-manage'],

            'Handwerk' => ['handwerk-view', 'handwerk-create', 'handwerk-assign', 'handwerk-manage', 'handwerk-delete'],

            // Original names (Aktuell/Ausgemustert = active/decommissioned item
            // filters, Ändern = edit, Bewegen = move, Ausmustern = decommission,
            // Umbennen = rename, information = item detail view) - all real
            // InvAbItemController actions.
            'Geräte' => ['Aktuell', 'Ausgemustert', 'Ändern', 'Bewegen', 'Ausmustern', 'Umbennen', 'information'],
            // Erfassen_Auto/Erfassen_Manuell = automatic/manual intake capture;
            // Inventur = stock-take (inventurStoreFinal/roomInventur) - originally
            // its own category with nothing seeded under it, folded in here since
            // this taxonomy doesn't have a separate top-level Inventur category.
            'Erfassen' => ['Erfassen_Auto', 'Erfassen_Manuell', 'Inventur'],
            'Drucken' => ['Drucken_list', 'Drucken_ticket'],
            'Standort' => ['standort-view', 'standort-manage'],

            'Teil_info' => ['teilnehmer-view', 'teilnehmer-manage', 'umfrage-manage'],

            'Personal' => ['personal-view', 'personal-manage', 'termination-manage'],

            'Sekretariat' => ['sekretariat-groups-manage'],

            'Content' => ['content-manage'],
            'Aufgaben' => ['tasks-manage'],
            'Lizenzen' => ['license-manage'],
            'Matrix' => ['matrix-view'],
        ];

        foreach ($byCategory as $categoryName => $permissionNames) {
            $category = Permissioncategory::where('name', $categoryName)->first();

            if (! $category) {
                // Should never happen if PermissioncategoryTableSeeder ran first,
                // but fail loudly rather than silently seed an uncategorized permission.
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
