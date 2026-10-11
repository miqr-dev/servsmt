<?php

namespace App\Notifications;

use App\Support\EmployeePassword;
use App\Support\MailText;
use App\Support\Notify;
use App\Support\TicketMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * IT ticket mails (TicketController). Payload 'title':
 *   Neues Ticket | Neuer Teilnehmer -> all Super_Admin
 *   Zugewiesen an                   -> the assignee
 *   Erledigt                        -> the submitter
 *   Wiederhergestellt               -> all Super_Admin
 *   Neuer Mitarbeiter               -> mailboxes (Erledigt 2)
 *
 * 2026-10-12: built with App\Support\TicketMail (subject "[IT #123] … – …",
 * full names, recipient-aware text, facts table). The person who triggered
 * the mail is not mailed (actor, captured in the constructor). The in-app
 * part (toDatabase) is unchanged ("Neu für dich").
 */
class TicketNotification extends Notification
{
    use Queueable;

    protected $myData;

    /** who triggered it: ['id', 'name', 'role'] (TicketMail::actor) */
    protected $actor;

    public function __construct($notifications)
    {
        $this->myData = $notifications;
        $this->actor = TicketMail::actor();
    }

    public function via($notifiable)
    {
        // No mails/notifications to users removed from AD (soft-deleted), nor
        // to the person who did it.
        if (Notify::isRemoved($notifiable) || Notify::isActor($notifiable, $this->actor)) {
            return [];
        }

        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $d = $this->myData;
        $id = $d['ticket_id'] ?? null;

        switch ($d['title'] ?? '') {
            case 'Neues Ticket':
            case 'Neuer Teilnehmer':
                return TicketMail::event('IT', $id, 'new', $notifiable, $d, $this->actor);
            case 'Zugewiesen an':
                return TicketMail::event('IT', $id, 'assigned', $notifiable, $d, $this->actor);
            case 'Erledigt':
                return TicketMail::event('IT', $id, 'done', $notifiable, $d, $this->actor);
            case 'Wiederhergestellt':
                return TicketMail::event('IT', $id, 'restored', $notifiable, $d, $this->actor);
            case 'Neuer Mitarbeiter':
                return $this->newEmployeeMail($d);
        }

        return TicketMail::message('IT', $id, $d['problem_type'] ?? '', (string) ($d['title'] ?? 'Benachrichtigung'))
            ->line(TicketMail::facts('IT', $id, TicketMail::model('IT', $id), $d['problem_type'] ?? '', $d['submitter'] ?? ''))
            ->action('Ticket öffnen', TicketMail::url('IT', $id));
    }

    /**
     * "Neuer Mitarbeiter" (Erledigt 2) to the ticked mailboxes. All user text
     * escaped (App\Support\MailText); the Windows password comes from
     * Einstellungen > Neuer Mitarbeiter (App\Support\EmployeePassword).
     */
    private function newEmployeeMail(array $d): MailMessage
    {
        $name = trim(($d['employee_firstname'] ?? '').' '.($d['employee_lastname'] ?? ''));
        $start = str_replace('-', '.', (string) ($d['employee_required_at'] ?? ''));
        $place = trim(implode(', ', array_filter([$d['employee_city'] ?? null, $d['employee_address'] ?? null])));
        $password = EmployeePassword::value();

        $intro = 'Ein neuer Mitarbeiter / eine neue Mitarbeiterin wurde angelegt'
            .($start !== '' ? ' und beginnt am <strong>'.MailText::e($start).'</strong>' : '')
            .($place !== '' ? ' in <strong>'.MailText::e($place).'</strong>' : '').'.';

        $mail = (new MailMessage)
            ->subject('[IT #'.($d['ticket_id'] ?? '').'] Neuer Mitarbeiter: '.($name !== '' ? $name : 'ohne Namen').($start !== '' ? ' – beginnt am '.$start : ''))
            ->greeting('Guten Tag,')
            ->salutation(TicketMail::salutation('IT'))
            ->line(MailText::p($intro))
            ->line(TicketMail::table([
                'Vorname' => $d['employee_firstname'] ?? null,
                'Nachname' => $d['employee_lastname'] ?? null,
                'Position' => $d['position_employee'] ?? null,
                'Abteilung' => $d['abteilung_employee'] ?? null,
                'Telefon' => $d['telephone_employee'] ?? null,
            ]))
            ->line(MailText::p('<strong>Windows-Anmeldung</strong>'))
            ->line(TicketMail::table([
                'Benutzername' => $d['employee_username'] ?? null,
                'Passwort' => $password !== '' ? $password : null,
                'E-Mail' => $d['employee_email'] ?? null,
            ]));

        if (! empty($d['isplus'])) {
            $mail->line(MailText::p('<strong>IS+:</strong> Zur Anmeldung an IS+ bitte den Haken „Windows-Authentifizierung“ setzen.'));
        }

        $notes = MailText::toText($d['message'] ?? '');
        if ($notes !== '') {
            $mail->line(MailText::p('<strong>Hinweise</strong>'))
                ->line(MailText::block($notes, true));
        }

        return $mail;
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->myData['title'],
            'id' => $this->myData['ticket_id'],
            'ersteller' => $this->myData['submitter'],
            'problem_type' => $this->myData['problem_type'],
        ];
    }
}
