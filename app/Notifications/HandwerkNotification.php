<?php

namespace App\Notifications;

use App\Support\Notify;
use App\Support\TicketMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Handwerk ticket mails (HandwerkController). Payload 'title':
 *   Neues Handwerk Ticket -> Handwerk_verwaltung
 *   Zugewiesen an         -> the assignee (+ ticket PDF with "PDF per Mail")
 *   Erledigt              -> the submitter; Handwerk_verwaltung when
 *                            Sekretariat / Handwerker closed it
 *   Wiederhergestellt     -> Handwerk_verwaltung
 *
 * 2026-10-12: App\Support\TicketMail. The text depends on the recipient, so
 * Handwerk_verwaltung no longer reads "Ihr eingereichtes Ticket …" but
 * "Erika Muster (Sekretariat) hat das Handwerk-Ticket von Max Mustermann als
 * erledigt markiert". Not mailed to the person who did it.
 */
class HandwerkNotification extends Notification
{
    use Queueable;

    protected $myData;

    protected $actor;

    public function __construct($notifications)
    {
        $this->myData = $notifications;
        $this->actor = TicketMail::actor();
    }

    public function via($notifiable)
    {
        if (Notify::isRemoved($notifiable) || Notify::isActor($notifiable, $this->actor)) {
            return [];
        }

        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $d = $this->myData;
        $id = $d['ticket_id'] ?? null;
        $event = match ($d['title'] ?? '') {
            'Neues Handwerk Ticket' => 'new',
            'Zugewiesen an' => 'assigned',
            'Erledigt' => 'done',
            'Wiederhergestellt' => 'restored',
            default => (string) ($d['title'] ?? 'Benachrichtigung'),
        };
        $mail = TicketMail::event('Handwerk', $id, $event, $notifiable, $d, $this->actor);

        if ($event === 'assigned' && ! empty($d['pdf_path']) && is_file($d['pdf_path'])) {
            $mail->attach($d['pdf_path'], [
                'as' => 'handwerk_ticket_'.$id.'.pdf',
                'mime' => 'application/pdf',
            ]);
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
