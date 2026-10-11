<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        //
    ];

    protected function schedule(Schedule $schedule)
    {
      // Sync users from Active Directory every six hours.
      //   --restore        re-activate users who are back / enabled in AD again
      //   --delete         soft-delete users whose AD account is disabled
      //   --delete-missing soft-delete users that are no longer returned by the
      //                    import at all (deleted from AD or out of the import
      //                    group). Without this, users removed from AD stayed in
      //                    Servsmt forever. Only affects users with an LDAP guid;
      //                    older rows without one are handled by
      //                    `php artisan ldap:missing-users` (report / --apply).
      // Always a SOFT delete: old tickets keep their creator/assignee names.
        $schedule->command('ldap:import ldap', [
          '--no-interaction',
          '--restore',
          '--delete',
          '--delete-missing',
      ])->everySixHours();

      // Ticket reminders: switched off 2026-10-12 - the button is gone from the
      // ticket page and the mail was English / failed for deleted tickets. A
      // global reminder comes later (then remove ShowReminders + ReminderNotification).
      // $schedule->command('reminders:show')->everySixHours();

      // Korso tickets of users without Korso_ma / Korso_Admin (or deactivated
      // by ldap:import) back to "Nicht zugewiesen" - logged (2026-10-08).
      $schedule->command('korso:release-assignments')->dailyAt('02:30');

      // Rooms (AD OUs) + computers from AD -> ad_ous / ad_computers (read-only on AD).
      // Hourly; also "Jetzt aktualisieren" on /ad-inventory.
      $schedule->command('ad:import-inventory')->hourly()->withoutOverlapping();

      // Profile -> Active Directory write-back: retry failed ones (does
      // nothing while AD_WRITEBACK is off). Hourly, so a failed change
      // normally reaches AD before the next ldap:import reads AD back into
      // servsmt (which would otherwise overwrite the profile edit).
      $schedule->command('ad:writeback --failed')->hourly()->withoutOverlapping();

      // Mails go through the queue (App\Support\Notify, QUEUE_CONNECTION=database):
      // send what is waiting, every minute; stops as soon as the queue is
      // empty, failed mails are retried 3 times (then table failed_jobs).
      $schedule->command('queue:work --stop-when-empty --tries=3 --backoff=60 --max-time=50')
        ->everyMinute()
        ->withoutOverlapping(10);

      // Default password for new employees not changed yet this year -> mail
      // to the Super_Admins, 1-7 January 07:00 (stops once it is saved).
      $schedule->command('servsmt:employee-password-reminder')->cron('0 7 1-7 1 *');
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
