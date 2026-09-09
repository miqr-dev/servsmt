<?php

use Illuminate\Database\Seeder;
use App\Permissioncategory;

/**
 * The 19 permission categories used to group checkboxes in
 * roles.create / roles.edit, mapped 1:1 from the route file's own
 * section comments (see servsmt_permission_taxonomy_proposal.md).
 * idempotent - safe to re-run.
 */
class PermissioncategoryTableSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'Rollen',
            'Berechtigungen',
            'Benutzer',
            'Einstellungen',
            'Ticket',
            'Korso',
            'Korso Marketing',
            'Handwerk',
            'Geräte',
            'Erfassen',
            'Drucken',
            'Standort',
            'Teil_info',
            'Personal',
            'Sekretariat',
            'Content',
            'Aufgaben',
            'Lizenzen',
            'Matrix',
        ];

        foreach ($categories as $name) {
            Permissioncategory::firstOrCreate(['name' => $name]);
        }
    }
}
