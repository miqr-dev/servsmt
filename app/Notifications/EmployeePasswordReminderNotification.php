<?php

namespace App\Notifications;

use App\Support\EmployeePassword;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * "Standard-Passwort für neue Mitarbeiter ändern" - mail only, sent by
 * servsmt:employee-password-reminder on 1-7 January. The password itself
 * is not in the mail.
 */
class EmployeePasswordReminderNotification extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        if (\App\Support\Notify::isRemoved($notifiable)) {
            return [];
        }

        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $year = now()->year;
        $changed = EmployeePassword::changedAt()?->format('d.m.Y');

        return (new MailMessage)
            ->subject("Neues Jahr: Standard-Passwort für neue Mitarbeiter ändern ({$year})")
            ->line("Das Standard-Windows-Passwort für neue Mitarbeiter wurde {$year} noch nicht geändert".($changed ? " (zuletzt am {$changed})." : '.'))
            ->line('Bitte im Active Directory ändern und danach unter Einstellungen › Neuer Mitarbeiter eintragen. Es wird mit der E-Mail „Neuer Mitarbeiter“ verschickt.')
            ->action('Jetzt ändern', route('settings.new_employee'));
    }
}
