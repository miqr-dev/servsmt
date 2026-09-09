<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use LdapRecord\Laravel\Events\Import\Imported;
use Spatie\Permission\Models\Role;

/**
 * Gives brand-new LDAP-authenticated users the 'Verwaltung' role by
 * default, so they have baseline access from their very first login.
 *
 * Why this event and only this event:
 * LdapRecord\Laravel\Auth\DatabaseUserProvider::validateCredentials()
 * does `$user->save();` and THEN, only `if ($user->wasRecentlyCreated)`,
 * fires this Imported event - i.e. exactly once, only the very first time
 * a given LDAP identity is synced into the local `users` table, and only
 * after the row has a real ID (safe to assignRole() against).
 *
 * This deliberately does NOT run for the local-dev auth bypass
 * (App\Http\Middleware\LocalDevAuthBypass): that middleware logs a user
 * in directly via Auth::login() on an already-existing local row, and
 * never goes through LDAP credential validation, so it never fires this
 * event. Existing users who already have roles are also never touched -
 * this only ever fires for a fresh insert.
 */
class AssignDefaultRoleToNewLdapUser
{
    public function handle(Imported $event)
    {
        $user = $event->eloquent;

        if (! Role::where('name', 'Verwaltung')->where('guard_name', 'web')->exists()) {
            Log::warning(
                "Skipped assigning default 'Verwaltung' role to new user #{$user->id}: "
                . "role does not exist yet - run the RoleTableSeeder (php artisan db:seed --class=RoleTableSeeder)."
            );

            return;
        }

        $user->assignRole('Verwaltung');
    }
}
