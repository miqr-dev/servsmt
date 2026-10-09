<?php

namespace App\Support;

use App\Korso;
use App\User;
use Illuminate\Support\Facades\Log;

/**
 * Open Korso tickets must not stay assigned to someone who can no longer
 * work on them (2026-10-08). Allowed assignees: users who really have
 * Korso_ma or Korso_Admin and are not deactivated (soft-deleted).
 *
 * Replaces the UPDATE that KorsoController@dashboard ran on EVERY page visit
 * (only Korso_ma counted there, so tickets taken by a Korso_Admin without
 * Korso_ma were silently un-assigned, and nothing was logged). Now it runs
 *  - right away when Korso_ma / Korso_Admin is removed from someone or a
 *    user is deleted in the app  -> release($userId)
 *  - once a night for everything else (e.g. users deactivated by the
 *    ldap:import)                  -> `php artisan korso:release-assignments`
 * Every un-assignment is written to the log (storage/logs/laravel*.log,
 * "Korso: Zuweisung entfernt").
 */
class KorsoAssignments
{
    public const ROLES = ['Korso_ma', 'Korso_Admin'];

    /** IDs of users who may hold Korso tickets. */
    public static function allowedUserIds(): array
    {
        // only roles that exist (User::role() throws for an unknown role)
        $roles = \Spatie\Permission\Models\Role::whereIn('name', self::ROLES)->pluck('name')->all();
        if (! $roles) {
            return [];
        }

        return User::role($roles)->pluck('users.id')->map(fn ($id) => (int) $id)->unique()->values()->all();
    }

    /**
     * Un-assign open Korso tickets of users who are not allowed anymore.
     * $userId: only check that one user (after a role change); null: everyone.
     *
     * @return int number of tickets un-assigned
     */
    public static function release(?int $userId = null, string $reason = 'nightly check', bool $dryRun = false): int
    {
        $allowed = self::allowedUserIds();
        if ($userId !== null && in_array($userId, $allowed, true)) {
            return 0; // still allowed
        }

        // safety: nobody allowed at all looks like a broken role setup - never
        // un-assign every ticket because of that
        if ($userId === null && ! $allowed) {
            Log::warning('Korso: release skipped - no user has Korso_ma / Korso_Admin');

            return 0;
        }

        $query = Korso::query()->whereNotNull('assignedTo'); // open tickets only (soft deletes = Erledigt)
        if ($userId !== null) {
            $query->where('assignedTo', $userId);
        } else {
            $query->whereNotIn('assignedTo', $allowed);
        }

        $tickets = $query->get(['id', 'assignedTo', 'problem_type']);
        if ($tickets->isEmpty() || $dryRun) {
            return $tickets->count();
        }

        $names = User::withTrashed()->whereIn('id', $tickets->pluck('assignedTo')->unique())->get(['id', 'username'])->pluck('username', 'id');
        Korso::whereIn('id', $tickets->pluck('id'))->update(['assignedTo' => null]);

        foreach ($tickets as $t) {
            Log::info('Korso: Zuweisung entfernt', [
                'korso_id' => $t->id,
                'problem_type' => $t->problem_type,
                'was_assigned_to' => $names[$t->assignedTo] ?? $t->assignedTo,
                'reason' => $reason,
                'by' => optional(auth()->user())->username ?? 'system',
            ]);
        }

        return $tickets->count();
    }
}
