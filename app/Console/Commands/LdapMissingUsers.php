<?php

namespace App\Console\Commands;

use App\Ldap\Scopes\ImportFilter;
use App\Ldap\User as LdapUser;
use App\User;
use Illuminate\Console\Command;

/**
 * Finds Servsmt users that are no longer (validly) in Active Directory, and
 * optionally soft-deletes them.
 *
 *   php artisan ldap:missing-users              -> report only, changes nothing
 *   php artisan ldap:missing-users --apply      -> soft-delete the users listed
 *   php artisan ldap:missing-users --all-ad     -> compare against ALL enabled
 *                                                  AD user accounts instead of
 *                                                  only the import group
 *
 * Why this exists next to `ldap:import --delete-missing` (which the scheduler
 * now also runs): --delete-missing only looks at users that carry an LDAP
 * guid + domain "default" from a previous LdapRecord import. Rows created
 * any other way (older import, manual entry) have no guid and would stay
 * forever. This command matches by guid first and falls back to the
 * username (sAMAccountName), so it catches those too.
 *
 * "Valid" = found in AD, account enabled, and (unless --all-ad) matched by
 * the same filter the import uses (App\Ldap\Scopes\ImportFilter: member of
 * the "Verwaltung" distribution group).
 *
 * Removal is a SOFT delete (deleted_at): the user disappears from lists,
 * pickers, notifications and login, but old tickets/comments keep showing
 * their name. If they come back into AD, the regular import (--restore)
 * brings them back.
 */
class LdapMissingUsers extends Command
{
    protected $signature = 'ldap:missing-users
        {--apply : Soft-delete the users that are not valid in AD (default: report only)}
        {--all-ad : Treat every enabled AD user account as valid, not only the import group}
        {--min=50 : Safety net - abort if AD returns fewer valid accounts than this}';

    protected $description = 'List (and optionally soft-delete) Servsmt users that are no longer valid in Active Directory';

    public function handle(): int
    {
        $query = LdapUser::query()
            ->select(['objectguid', 'samaccountname', 'useraccountcontrol'])
            ->rawFilter('(objectCategory=person)'); // users only, never computer accounts

        if ($this->option('all-ad')) {
            $query->withoutGlobalScope(ImportFilter::class);
        }

        $this->info('Reading Active Directory …');
        $ldapUsers = $query->paginate(1000);

        $validGuids = [];
        $validUsernames = [];
        foreach ($ldapUsers as $ldapUser) {
            if ($ldapUser->isDisabled()) {
                continue;
            }
            if ($guid = $ldapUser->getConvertedGuid()) {
                $validGuids[strtolower($guid)] = true;
            }
            if ($sam = $ldapUser->getFirstAttribute('samaccountname')) {
                $validUsernames[strtolower($sam)] = true;
            }
        }

        $validCount = count($validUsernames);
        $this->info("Valid AD accounts: {$validCount}" . ($this->option('all-ad') ? ' (all enabled AD users)' : ' (import group)'));

        // An empty or tiny result almost always means a connection / filter
        // problem, not that everyone left - never mass-delete on that.
        if ($validCount < (int) $this->option('min')) {
            $this->error("Only {$validCount} valid AD accounts found (minimum --min={$this->option('min')}). Aborting, nothing changed.");
            return self::FAILURE;
        }

        $missing = User::query() // active (not soft-deleted) users only
            ->orderBy('name')
            ->get(['id', 'username', 'name', 'vorname', 'email', 'guid', 'lastlogin'])
            ->filter(function ($user) use ($validGuids, $validUsernames) {
                if ($user->guid && isset($validGuids[strtolower($user->guid)])) {
                    return false;
                }
                return ! ($user->username && isset($validUsernames[strtolower($user->username)]));
            })
            ->values();

        if ($missing->isEmpty()) {
            $this->info('All active Servsmt users are valid in AD. Nothing to do.');
            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Benutzername', 'Name', 'E-Mail', 'LDAP-guid', 'Letzter Login'],
            $missing->map(fn ($u) => [
                $u->id,
                $u->username,
                trim(($u->name ?? '') . ', ' . ($u->vorname ?? ''), ', '),
                $u->email,
                $u->guid ? 'ja' : 'nein',
                $u->lastlogin ?? '',
            ])->all()
        );
        $this->warn("{$missing->count()} active Servsmt user(s) are not valid in AD.");

        if (! $this->option('apply')) {
            $this->line('Report only - nothing changed. Run again with --apply to soft-delete them.');
            return self::SUCCESS;
        }

        if ($this->input->isInteractive() && ! $this->confirm("Soft-delete these {$missing->count()} users now?")) {
            $this->line('Cancelled, nothing changed.');
            return self::SUCCESS;
        }

        User::whereIn('id', $missing->pluck('id'))->each(function ($user) {
            $user->delete(); // SoftDeletes -> sets deleted_at
        });

        $this->info("Soft-deleted {$missing->count()} user(s).");
        return self::SUCCESS;
    }
}
