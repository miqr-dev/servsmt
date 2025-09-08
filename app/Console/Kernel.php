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
      // Import LDAP users hourly.
        $schedule->command('ldap:import ldap', [
          '--no-interaction',
          '--restore',
          '--delete'
      ])->everySixHours();

      $schedule->command('reminders:show')->everySixHours();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
