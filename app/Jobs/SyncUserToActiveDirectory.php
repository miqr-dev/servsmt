<?php

namespace App\Jobs;

use App\AdSyncLog;
use App\Support\ActiveDirectoryWriteback;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Writes a user's profile fields to their Active Directory account and
 * records the result in the given ad_sync_logs row.
 *
 * Always writes the CURRENT servsmt values (not a snapshot), so a retry
 * never pushes outdated data.
 *
 * - With a real queue (QUEUE_CONNECTION=database/redis + queue:work) a
 *   failure is retried automatically (5 tries, 1 min ... 1 h apart).
 * - With QUEUE_CONNECTION=sync it runs during the save; a failure is only
 *   logged (the save itself still succeeds) and `ad:writeback --failed`
 *   (scheduled hourly) retries it.
 */
class SyncUserToActiveDirectory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(public int $logId)
    {
    }

    public function backoff(): array
    {
        return [60, 300, 900, 3600];
    }

    public function handle(): void
    {
        $log = AdSyncLog::find($this->logId);
        if (! $log) {
            return;
        }
        $log->attempts++;

        $user = User::withTrashed()->find($log->user_id);
        if (! $user) {
            $log->fill(['status' => AdSyncLog::NOT_FOUND, 'error' => 'Benutzer nicht in servsmt gefunden.'])->save();

            return;
        }

        try {
            $ad = ActiveDirectoryWriteback::find($user);
            if (! $ad) {
                // no retry - it will not appear by itself
                $log->fill(['status' => AdSyncLog::NOT_FOUND, 'error' => "Kein AD-Konto für {$user->username} gefunden."])->save();

                return;
            }

            $changes = ActiveDirectoryWriteback::diff($ad, ActiveDirectoryWriteback::valuesFor($user));
            ActiveDirectoryWriteback::apply($ad, $changes);

            $log->fill([
                'status' => AdSyncLog::SUCCESS,
                'changes' => $changes,
                'error' => null,
                'ad_dn' => $ad->getDn(),
                'synced_at' => now(),
            ])->save();
        } catch (\Throwable $e) {
            $log->fill(['status' => AdSyncLog::FAILED, 'error' => ActiveDirectoryWriteback::errorMessage($e)])->save();
            report($e);

            // real queue: let the worker retry; sync: never break the profile save
            if ($this->job && $this->job->getConnectionName() !== 'sync') {
                throw $e;
            }
        }
    }
}
