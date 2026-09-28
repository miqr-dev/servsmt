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

      $schedule->command('reminders:show')->everySixHours();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
