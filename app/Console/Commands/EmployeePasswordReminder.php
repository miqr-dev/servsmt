<?php

namespace App\Console\Commands;

use App\Notifications\EmployeePasswordReminderNotification;
use App\Support\EmployeePassword;
use App\Support\Notify;
use App\User;
use Illuminate\Console\Command;

/**
 * Mail to all Super_Admins when the default password for new employees was
 * not changed yet this year (App\Support\EmployeePassword). Scheduled for
 * 1-7 January, 07:00 (app/Console/Kernel.php); does nothing once it is saved.
 */
class EmployeePasswordReminder extends Command
{
    protected $signature = 'servsmt:employee-password-reminder';

    protected $description = 'Erinnert die Super_Admins, das Standard-Passwort für neue Mitarbeiter zu ändern (wenn dieses Jahr noch nicht geändert)';

    public function handle(): int
    {
        if (! EmployeePassword::isOutdated()) {
            $this->info('Standard-Passwort ist aktuell - keine Erinnerung.');

            return self::SUCCESS;
        }

        $admins = User::role('Super_Admin')->get();
        Notify::send($admins, new EmployeePasswordReminderNotification);
        $this->info('Erinnerung an '.$admins->count().' Super_Admin(s) geschickt.');

        return self::SUCCESS;
    }
}
