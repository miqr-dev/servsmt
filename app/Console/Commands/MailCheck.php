<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * E-mail status and switch-over helper (2026-10-12).
 *
 *   php artisan servsmt:mail-check                 status: mailer, sender, queue, waiting / failed mails, scheduler
 *   php artisan servsmt:mail-check --purge         delete waiting + failed MAILS from the queue (other jobs stay)
 *   php artisan servsmt:mail-check --send=a@b.de   send one test mail right now (not queued)
 *
 * Mails are queued (App\Support\Notify) and sent by the scheduler's
 * queue:work. Before switching from MAIL_MAILER=log to smtp, --purge makes
 * sure no old mails from the test phase go out in one burst.
 */
class MailCheck extends Command
{
    protected $signature = 'servsmt:mail-check
        {--purge : wartende und fehlgeschlagene Mails löschen (andere Jobs bleiben)}
        {--send= : jetzt eine Test-Mail an diese Adresse schicken (nicht über die Warteschlange)}';

    protected $description = 'E-Mail-Status (Versandart, Absender, Warteschlange, Scheduler); alte Mails löschen; Test-Mail senden';

    private const MAIL_JOB = '%SendQueuedNotifications%';

    public function handle(): int
    {
        $mailer = (string) config('mail.default');
        $queue = (string) config('queue.default');

        $this->line('');
        $this->info('Versand');
        $this->line('  MAIL_MAILER       : '.$mailer.match ($mailer) {
            'log' => '  -> E-Mails werden NICHT verschickt, nur in storage/logs geschrieben',
            'smtp' => '  -> echte E-Mails über SMTP',
            default => '',
        });
        if ($mailer === 'smtp') {
            $this->line('  Server            : '.config('mail.mailers.smtp.host').':'.config('mail.mailers.smtp.port')
                .' ('.(config('mail.mailers.smtp.encryption') ?: 'ohne Verschlüsselung').')'
                .(config('mail.mailers.smtp.username') ? ', Anmeldung: '.config('mail.mailers.smtp.username') : ', ohne Anmeldung'));
        }
        $from = (string) config('mail.from.address');
        $this->line('  Absender          : '.config('mail.from.name').' <'.$from.'>'
            .(in_array($from, ['', 'hello@example.com'], true) ? '  !! MAIL_FROM_ADDRESS fehlt' : ''));
        $this->line('  QUEUE_CONNECTION  : '.$queue.match ($queue) {
            'sync' => '  -> Mails gehen sofort beim Klick raus',
            'database' => '  -> Mails warten in der Tabelle jobs, der Scheduler schickt sie jede Minute',
            default => '',
        });
        if (app()->configurationIsCached()) {
            $this->line('  (Konfiguration ist gecacht: nach Änderungen an .env "php artisan optimize" ausführen)');
        }

        $this->line('');
        $this->info('Warteschlange');
        $waiting = DB::table('jobs')->where('payload', 'like', self::MAIL_JOB);
        $waitingCount = (clone $waiting)->count();
        $oldest = (clone $waiting)->min('created_at');
        $other = DB::table('jobs')->where('payload', 'not like', self::MAIL_JOB)->count();
        $failed = DB::table('failed_jobs')->where('payload', 'like', self::MAIL_JOB)->count();
        $this->line('  Wartende Mails    : '.$waitingCount
            .($oldest ? ' (älteste von '.Carbon::createFromTimestamp((int) $oldest)->format('d.m.Y H:i').')' : ''));
        $this->line('  Fehlgeschlagen    : '.$failed);
        $this->line('  Andere Jobs       : '.$other.' (z. B. AD-Rückschreiben, werden nicht angefasst)');

        $beat = Cache::get('servsmt:scheduler-heartbeat');
        $this->line('  Scheduler         : '.($beat
            ? 'zuletzt gelaufen '.Carbon::parse($beat)->diffForHumans()
            : 'keine Spur - läuft die Windows-Aufgabe "php artisan schedule:run" jede Minute?'));

        if ($this->option('purge')) {
            $this->purge($waitingCount, $failed);
        }

        if ($to = $this->option('send')) {
            return $this->sendTest((string) $to, $mailer);
        }

        return self::SUCCESS;
    }

    private function purge(int $waiting, int $failed): void
    {
        $this->line('');
        if ($waiting + $failed === 0) {
            $this->info('Keine alten Mails in der Warteschlange - nichts zu löschen.');

            return;
        }
        if (! $this->confirm("{$waiting} wartende und {$failed} fehlgeschlagene Mails löschen? Sie werden dann nie verschickt.", true)) {
            return;
        }
        $deleted = DB::table('jobs')->where('payload', 'like', self::MAIL_JOB)->delete();
        $deletedFailed = DB::table('failed_jobs')->where('payload', 'like', self::MAIL_JOB)->delete();
        $this->info("Gelöscht: {$deleted} wartende, {$deletedFailed} fehlgeschlagene Mails.");
    }

    private function sendTest(string $to, string $mailer): int
    {
        $this->line('');
        if (! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->error("Keine gültige Adresse: {$to}");

            return self::FAILURE;
        }
        try {
            Mail::raw(
                "Test-E-Mail von Servsmt (".now()->format('d.m.Y H:i').").\n\nWenn diese Mail ankommt, ist der Versand richtig eingerichtet.",
                fn ($m) => $m->to($to)->subject('Servsmt: Test-E-Mail')
            );
        } catch (\Throwable $e) {
            $this->error('Senden fehlgeschlagen: '.$e->getMessage());
            $this->line('Prüfen: MAIL_HOST, MAIL_PORT, MAIL_ENCRYPTION, MAIL_USERNAME / MAIL_PASSWORD; bei Zertifikatsfehlern MAIL_VERIFY_PEER=false.');

            return self::FAILURE;
        }
        $this->info($mailer === 'log'
            ? "Test-Mail an {$to} in storage/logs geschrieben (MAIL_MAILER=log - nichts verschickt)."
            : "Test-Mail an {$to} verschickt. Bitte Posteingang prüfen.");

        return self::SUCCESS;
    }
}
