<?php

namespace App\Console\Commands;

use App\AdSyncLog;
use App\Jobs\SyncUserToActiveDirectory;
use App\Support\ActiveDirectoryWriteback;
use App\User;
use Illuminate\Console\Command;

/**
 * Profile -> Active Directory write-back from the console.
 *
 *   php artisan ad:writeback jdoe --dry-run   show what WOULD change in AD (writes nothing)
 *   php artisan ad:writeback jdoe             write jdoe's profile to AD now
 *   php artisan ad:writeback --failed         retry users whose last write-back failed
 *                                             (scheduled hourly, see Console\Kernel)
 */
class AdWriteback extends Command
{
    protected $signature = 'ad:writeback
        {username? : samaccountname (servsmt Benutzername)}
        {--failed : retry users whose last write-back failed}
        {--dry-run : only show the differences, write nothing}';

    protected $description = 'Write servsmt profile fields to Active Directory';

    public function handle(): int
    {
        if ($this->option('failed')) {
            return $this->retryFailed();
        }

        $username = $this->argument('username');
        if (! $username) {
            $this->error('Benutzername oder --failed angeben.');

            return self::INVALID;
        }

        $user = User::where('username', $username)->first();
        if (! $user) {
            $this->error("Benutzer {$username} nicht in servsmt gefunden.");

            return self::FAILURE;
        }

        if ($this->option('dry-run')) {
            return $this->dryRun($user);
        }

        $log = AdSyncLog::create(['user_id' => $user->id, 'status' => AdSyncLog::PENDING]);
        SyncUserToActiveDirectory::dispatchSync($log->id);
        $log->refresh();
        $this->line("Status: {$log->status}".($log->error ? " - {$log->error}" : ''));
        $this->printChanges($log->changes ?? []);

        return $log->status === AdSyncLog::SUCCESS ? self::SUCCESS : self::FAILURE;
    }

    private function dryRun(User $user): int
    {
        $ad = ActiveDirectoryWriteback::find($user);
        if (! $ad) {
            $this->error("Kein AD-Konto für {$user->username} gefunden.");

            return self::FAILURE;
        }
        $this->line('AD-Konto: '.$ad->getDn());
        $changes = ActiveDirectoryWriteback::diff($ad, ActiveDirectoryWriteback::valuesFor($user));
        $this->printChanges($changes);
        $this->comment('Dry run - nichts geschrieben.');

        return self::SUCCESS;
    }

    private function retryFailed(): int
    {
        if (! ActiveDirectoryWriteback::enabled()) {
            return self::SUCCESS;
        }
        // latest log per user; retry only if that one failed and is recent
        $latestIds = AdSyncLog::selectRaw('MAX(id)')->groupBy('user_id');
        $failed = AdSyncLog::whereIn('id', $latestIds)
            ->where('status', AdSyncLog::FAILED)
            ->where('created_at', '>=', now()->subDays(config('ad_writeback.retry_days')))
            ->get();

        foreach ($failed as $old) {
            $log = AdSyncLog::create(['user_id' => $old->user_id, 'changed_by' => $old->changed_by, 'status' => AdSyncLog::PENDING]);
            SyncUserToActiveDirectory::dispatchSync($log->id);
            $log->refresh();
            $this->line("user {$old->user_id}: {$log->status}".($log->error ? " - {$log->error}" : ''));
        }
        $this->info($failed->count().' erneut versucht.');

        return self::SUCCESS;
    }

    private function printChanges(array $changes): void
    {
        if (! $changes) {
            $this->info('Keine Unterschiede - AD ist aktuell.');

            return;
        }
        $this->table(['Attribut', 'AD (alt)', 'servsmt (neu)'], collect($changes)->map(
            fn ($c, $attr) => [$attr, $c['old'] ?? '(leer)', $c['new'] ?? '(leer - wird gelöscht)']
        )->values()->all());
    }
}
