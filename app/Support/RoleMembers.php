<?php

namespace App\Support;

use App\User;
use Illuminate\Support\Collection;

/**
 * Roles whose members can be added / removed directly on the
 * Rollen & Berechtigungen > Handwerk / Korso pages (RoleMemberController,
 * components/RoleMembersCard.vue). Only these - everything else stays in
 * Benutzer > Bearbeiten.
 */
class RoleMembers
{
    public const MANAGED = ['Handwerk_verwaltung', 'Korso_verwaltung', 'Korso_Admin', 'Printmarketing', 'Onlinemarketing'];

    /** Users that really have the role (Super_Admin is not listed implicitly). */
    public static function of(string $role): Collection
    {
        return User::role($role)->get()
            ->map(fn ($u) => self::option($u))
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();
    }

    /** All active users for the "add" pickers. */
    public static function allUsers(): Collection
    {
        return User::orderBy('name')->orderBy('vorname')->get(['id', 'vorname', 'name', 'username', 'ort'])
            ->map(fn ($u) => self::option($u));
    }

    public static function option($u): array
    {
        return [
            'id' => $u->id,
            'name' => trim($u->vorname.' '.$u->name) ?: $u->username,
            'username' => $u->username,
            'ort' => $u->ort,
        ];
    }
}
