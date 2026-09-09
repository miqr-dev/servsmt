<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleTableSeeder extends Seeder
{
    /**
     * Every role name referenced anywhere in the app (controllers,
     * middleware role: checks, User::role() queries). Creates each one
     * if it doesn't already exist yet - safe to re-run.
     *
     * Note: there is a pre-existing casing inconsistency in the codebase
     * for the Korso assignee role - most places use 'Korso_ma', but
     * KorsoController::filterTickets() queries the lowercase 'korso_ma'.
     * We seed the canonical 'Korso_ma' (majority usage, and the one
     * actually granted via assignRole()) rather than creating a second
     * role to paper over that bug - that call site should be fixed in
     * code separately.
     */
    public function run()
    {
        $roles = [
            'Super_Admin',
            'HR',
            'Korso_Admin',
            'Korso_ma',
            'handwerk_admin',
            'Verwaltung',
            'Sekretariat',
            'INV',
            'Teilnehmer_Info',
        ];

        foreach ($roles as $name) {
            Role::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }
    }
}
