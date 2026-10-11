<?php

namespace App\Notifications;

use App\Support\Notify;
use App\Support\TicketMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Korso ticket mails (KorsoController). Payload 'title':
 *   Neues Korso Ticket -> Korso_ma (priority Hoch), role Onlinemarketing
 *                         (every Onlinemarketing ticket)
 *   Zugewiesen an      -> the assignee
 *   Erledigt           -> the submitter or the Sekretariat group mailbox
 *   Wiederhergestellt  -> assignee / Korso_verwaltung, or the submitter
 *
 * 2026-10-12: App\Support\TicketMail - the mails now name the ticket
 * (number, type, submitter); priority comes from the ticket, so the subject
 * only says "Priorität Hoch" when it is. Signed "Ihr Korso-Team".
 */
class KorsoNotification extends Notification
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
        $id = $d['korso_id'] ?? null;
        $event = match ($d['title'] ?? '') {
            'Neues Korso Ticket' => 'new',
            'Zugewiesen an' => 'assigned',
            'Erledigt' => 'done',
            'Wiederhergestellt' => 'restored',
            default => (string) ($d['title'] ?? 'Benachrichtigung'),
        };
        $mail = TicketMail::event('Korso', $id, $event, $notifiable, $d, $this->actor);

        if ($event === 'new' && (int) optional(TicketMail::model('Korso', $id))->priority === 3) {
            $mail->subject($mail->subject.' (Priorität Hoch)');
        }

        return $mail;
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->myData['title'],
            'korso_id' => $this->myData['korso_id'],
            'submitter' => $this->myData['submitter'],
            'problem_type' => $this->myData['problem_type'],
        ];
    }

    public function toArray($notifiable)
    {
        return [];
    }
}
