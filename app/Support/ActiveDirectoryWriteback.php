<?php

namespace App\Support;

use App\User;
use LdapRecord\Models\ActiveDirectory\User as AdUser;

/**
 * Profile -> Active Directory write-back (config/ad_writeback.php).
 *
 * Reads the servsmt user's profile columns, compares them with the AD
 * account and writes only the attributes that differ. Only the attributes
 * listed in config('ad_writeback.attributes') can ever be written.
 * Uses the base LdapRecord AD model on the "writer" connection (not
 * App\Ldap\User, whose import scope would hide accounts outside the
 * Verwaltung group).
 */
class ActiveDirectoryWriteback
{
    public static function enabled(): bool
    {
        return (bool) config('ad_writeback.enabled');
    }

    /** AD attribute => value from servsmt (null = clear the attribute in AD). */
    public static function valuesFor(User $user): array
    {
        $values = [];
        foreach (config('ad_writeback.attributes') as $column => $attribute) {
            $value = $user->getAttribute($column);
            $value = is_string($value) ? trim($value) : $value;
            $values[$attribute] = ($value === null || $value === '') ? null : (string) $value;
        }

        return $values;
    }

    /** The user's AD account: by objectGUID (set by ldap:import), else samaccountname. */
    public static function find(User $user): ?AdUser
    {
        $connection = config('ad_writeback.connection');
        $found = null;

        if ($user->guid) {
            $found = AdUser::on($connection)->findByGuid($user->guid);
        }
        if (! $found && $user->username) {
            $found = AdUser::on($connection)->whereEquals('samaccountname', $user->username)->first();
        }

        return $found ? $found->setConnection($connection) : null;
    }

    /** {attribute: {old, new}} for every attribute whose value differs. */
    public static function diff(AdUser $ad, array $values): array
    {
        $changes = [];
        foreach ($values as $attribute => $new) {
            $old = $ad->getFirstAttribute($attribute);
            $old = is_string($old) ? trim($old) : $old;
            if ((string) $old === (string) $new) {
                continue;
            }
            $changes[$attribute] = ['old' => $old, 'new' => $new];
        }

        return $changes;
    }

    /** Writes the changes in one LDAP modify operation. */
    public static function apply(AdUser $ad, array $changes): void
    {
        if (! $changes) {
            return;
        }
        foreach ($changes as $attribute => $change) {
            // [] removes the attribute (AD rejects empty strings)
            $ad->setAttribute($attribute, $change['new'] === null ? [] : $change['new']);
        }
        $ad->save();
    }

    /** Readable error incl. the AD diagnostic message (e.g. "INSUFF_ACCESS_RIGHTS"). */
    public static function errorMessage(\Throwable $e): string
    {
        $message = $e->getMessage();
        if (method_exists($e, 'getDetailedError') && ($detail = $e->getDetailedError())) {
            $diagnostic = trim((string) $detail->getDiagnosticMessage());
            if ($diagnostic !== '' && ! str_contains($message, $diagnostic)) {
                $message .= ' - '.$diagnostic;
            }
        }

        return mb_substr($message, 0, 1000);
    }
}
