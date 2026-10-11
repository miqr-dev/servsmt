<?php

namespace App\Notifications;

use App\Support\MailText;
use App\Support\Notify;
use App\Support\TicketMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * New comment or reply on an IT, Handwerk or Korso ticket
 * (CommentController::notifyAbout, which also decides who gets it - never
 * the writer).
 *
 * Mail (2026-10-11/12), by payload 'mail_kind':
 *   comment      "[IT #123] PC-Probleme – neuer Kommentar von Max Mustermann"
 *   reply        "… – neue Antwort von …" (to the other people of the ticket)
 *   reply_to_you "… – Antwort auf Ihren Kommentar von …" (author of the answered comment)
 * The comment is escaped and keeps its line breaks (App\Support\MailText);
 * signed by the team of the system (TicketMail).
 *
 * Payload: title ('neuer Kommentar' | 'Kommentarantwort', for the in-app
 * entry), id, type ('Ticket' | 'Handwerk' | 'Korso'), comment, commenter_id
 * (username), commenter_name, ticket_title, mail_kind.
 */
class CommentNotification extends Notification
{
    use Queueable;

    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function via($notifiable)
    {
        // No mails/notifications to users removed from AD (soft-deleted).
        if (Notify::isRemoved($notifiable)) {
            return [];
        }

        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $system = TicketMail::system($this->data['type'] ?? 'Ticket');
        $id = $this->data['id'];
        $who = trim((string) ($this->data['commenter_name'] ?? '')) ?: (string) ($this->data['commenter_id'] ?? '');
        $title = trim((string) ($this->data['ticket_title'] ?? ''));
        $kind = $this->data['mail_kind'] ?? (($this->data['title'] ?? '') === 'Kommentarantwort' ? 'reply_to_you' : 'comment');

        $event = match ($kind) {
            'reply_to_you' => 'Antwort auf Ihren Kommentar',
            'reply' => 'neue Antwort',
            default => 'neuer Kommentar',
        }.($who !== '' ? ' von '.$who : '');

        $ticket = TicketMail::label($system).' <strong>#'.MailText::e((string) $id).'</strong>'
            .($title !== '' ? ' „'.MailText::e($title).'“' : '');
        $name = '<strong>'.MailText::e($who !== '' ? $who : 'Jemand').'</strong>';
        $intro = match ($kind) {
            'reply_to_you' => $name.' hat auf Ihren Kommentar zum '.$ticket.' geantwortet:',
            'reply' => $name.' hat im '.$ticket.' auf einen Kommentar geantwortet:',
            default => $name.' hat einen neuen Kommentar zum '.$ticket.' geschrieben:',
        };

        return TicketMail::message($system, $id, $title, $event)
            ->line(MailText::p($intro))
            ->line(MailText::block($this->data['comment'] ?? '', true))
            ->action('Ticket öffnen', TicketMail::url($system, $id));
    }

    public function toDatabase($notifiable)
    {
        return $this->toArray($notifiable);
    }

    public function toArray($notifiable)
    {
        return [
            'title' => $this->data['title'],
            'id' => $this->data['id'], // commentable_id
            'comment' => $this->data['comment'],
            'commenter' => $this->data['commenter_id'],
            'type' => strtolower($this->data['type'] ?? 'ticket'),
        ];
    }
}
