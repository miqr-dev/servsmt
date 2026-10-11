<?php

namespace App\Support;

use App\Handwerk;
use App\Korso;
use App\Ticket;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;

/**
 * One German layout for all ticket mails - IT, Handwerk, Korso (2026-10-12).
 *
 * Before, every system wrote its own lines: generic subjects
 * ("Ticketbenachrichtigung", "Handwerkerticket benachrichtigung"), the ticket
 * number only inside the link, usernames instead of names, Sie/du mixed, the
 * same "Ihr eingereichtes Ticket …" text for every recipient.
 *
 * Now every mail has
 *  - subject "[IT #123] PC-Probleme – Ihnen zugewiesen";
 *  - one sentence what happened, written for the recipient: the submitter
 *    reads "Ihr Handwerk-Ticket wurde von Erika Muster als erledigt markiert",
 *    everyone else "Erika Muster (Sekretariat) hat das Handwerk-Ticket von Max
 *    Mustermann als erledigt markiert";
 *  - a small table: ticket, type, created by, location, created at, priority;
 *  - button "Ticket öffnen", signature of the team (IT / Handwerk / Korso).
 * The "do not reply" note is in the footer of every mail
 * (resources/views/vendor/mail/{html,text}/message.blade.php).
 *
 * Names and data are read from the ticket when the mail is built (also in the
 * queue worker); the payload of the notification is the fallback, e.g. for a
 * ticket that was deleted for good. The person who triggered the mail
 * ("actor") is captured when the notification is created, see actor().
 *
 * Links keep the fixed host (decision 2026-10-11: the production server is
 * renamed to servsmt). One place to change it: config('mail.ticket_link_base').
 */
class TicketMail
{
    public const BASE_URL = 'http://servsmt.miqr.local';

    private const SYSTEMS = [
        'IT' => ['path' => 'ticket', 'label' => 'IT-Ticket', 'team' => 'Ihre IT'],
        'Handwerk' => ['path' => 'handwerk', 'label' => 'Handwerk-Ticket', 'team' => 'Ihr Handwerk-Team'],
        'Korso' => ['path' => 'korso', 'label' => 'Korso-Ticket', 'team' => 'Ihr Korso-Team'],
    ];

    private const PRIORITIES = [1 => 'Niedrig', 2 => 'Normal', 3 => 'Hoch'];

    public static function system(?string $type): string
    {
        return MailText::system($type);
    }

    public static function label(string $system): string
    {
        return self::SYSTEMS[$system]['label'] ?? 'Ticket';
    }

    public static function url(string $system, $id): string
    {
        $base = rtrim((string) config('mail.ticket_link_base', self::BASE_URL), '/');

        return $base.'/'.(self::SYSTEMS[$system]['path'] ?? 'ticket').'/'.$id;
    }

    public static function salutation(string $system): string
    {
        return "Mit freundlichen Grüßen\n".(self::SYSTEMS[$system]['team'] ?? 'Ihre IT');
    }

    /** The ticket (also when done / soft-deleted), or null. */
    public static function model(string $system, $id)
    {
        if (! $id) {
            return null;
        }
        $class = match ($system) {
            'Handwerk' => Handwerk::class,
            'Korso' => Korso::class,
            default => Ticket::class,
        };

        try {
            return $class::withTrashed()->find($id);
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /** "Max Mustermann" (username when no name is stored), '' for null. */
    public static function person($user): string
    {
        if (! $user) {
            return '';
        }

        return trim(($user->vorname ?? '').' '.($user->name ?? '')) ?: (string) ($user->username ?? '');
    }

    /**
     * The logged-in user who triggers the mail, as plain data (queue-safe):
     * ['id' => 5, 'name' => 'Erika Muster', 'role' => 'Sekretariat' | 'Handwerker' | null].
     */
    public static function actor(): ?array
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }
        $role = null;
        if (method_exists($user, 'hasAssignedRole')) {
            if ($user->hasAssignedRole('Sekretariat')) {
                $role = 'Sekretariat';
            } elseif ($user->hasAssignedRole('handwerk')) {
                $role = 'Handwerker';
            }
        }

        return ['id' => (int) $user->id, 'name' => self::person($user), 'role' => $role];
    }

    /** True when the recipient is the submitter (or a group mailbox that submitted it). */
    public static function isSubmitter($notifiable, $model): bool
    {
        if ($notifiable instanceof AnonymousNotifiable) {
            return true; // Sekretariat group mailbox = submitted "als Sekretariat"
        }

        return $model && is_object($notifiable) && isset($notifiable->id) && (int) $notifiable->id === (int) $model->submitter;
    }

    /** Submitter's full name from the ticket, else the payload value. */
    public static function submitterName($model, ?string $fallback = null): string
    {
        $name = $model ? self::person($model->subUser) : '';

        return $name !== '' ? $name : (string) ($fallback ?? '');
    }

    /**
     * Facts table: Ticket, Art, Erstellt von, Standort, Erstellt am, Priorität.
     *
     * @param  array<string, string|null>  $extra  more rows after the defaults
     */
    public static function facts(string $system, $id, $model, ?string $title, ?string $submitter, array $extra = []): HtmlString
    {
        $rows = [
            'Ticket' => self::label($system).' #'.$id,
            'Art' => $title,
            'Erstellt von' => $submitter,
        ];
        if ($model) {
            $city = $model->getAttribute('problem_in_city')
                ?: ($model->getAttribute('submitter_standort') ?: optional(optional($model->location)->place)->pnname);
            $address = $model->getAttribute('submitter_adresse') ?: optional($model->location)->address;
            $rows['Standort'] = trim(implode(', ', array_filter([$city, $address])));
            $rows['Erstellt am'] = $model->created_at ? $model->created_at->format('d.m.Y, H:i').' Uhr' : null;
            $priority = $model->getAttribute('priority') ?? $model->getAttribute('priority_id');
            $rows['Priorität'] = $priority !== null ? (self::PRIORITIES[(int) $priority] ?? null) : null;
        }

        return self::table(array_merge($rows, $extra));
    }

    /** @param  array<string, string|null>  $rows  label => value (plain text, escaped here) */
    public static function table(array $rows): HtmlString
    {
        $tr = '';
        foreach ($rows as $label => $value) {
            if ($value === null || trim((string) $value) === '') {
                continue;
            }
            $tr .= '<tr><td style="padding:3px 16px 3px 0;color:#718096;white-space:nowrap;vertical-align:top;">'.MailText::e($label).'</td>'
                .'<td style="padding:3px 0;color:#2d3748;vertical-align:top;">'.MailText::e((string) $value).'</td></tr>';
        }

        return new HtmlString('<table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 20px;font-size:15px;line-height:1.5;">'.$tr.'</table>');
    }

    /**
     * Standard event mail.
     *
     * @param  string  $event  new | assigned | done | restored
     */
    public static function event(string $system, $id, string $event, $notifiable, array $payload, ?array $actor): MailMessage
    {
        $model = self::model($system, $id);
        $label = self::label($system);
        $title = trim((string) ($model->problem_type ?? $payload['problem_type'] ?? ''));
        $submitter = self::submitterName($model, $payload['submitter'] ?? null);
        $mine = self::isSubmitter($notifiable, $model);
        $who = (string) ($actor['name'] ?? '');
        $whoRole = $who !== '' && ! empty($actor['role']) ? $who.' ('.$actor['role'].')' : $who;
        $byActor = $who !== '' ? ' von <strong>'.MailText::e($who).'</strong>' : '';
        $own = $model && $actor && (int) ($actor['id'] ?? 0) === (int) $model->submitter; // the submitter did it
        $the = $own ? 'das eigene '.$label : 'das '.$label.($submitter !== '' ? ' von <strong>'.MailText::e($submitter).'</strong>' : '');
        $actorDid = $who !== '' ? '<strong>'.MailText::e($whoRole).'</strong> hat' : 'Es wurde';

        [$subjectEvent, $intro] = match ($event) {
            'new' => [
                'neues Ticket',
                ($submitter !== '' ? '<strong>'.MailText::e($submitter).'</strong> hat' : 'Es wurde').' ein neues '.$label.' erstellt.',
            ],
            'assigned' => [
                'Ihnen zugewiesen',
                $who !== ''
                    ? '<strong>'.MailText::e($who).'</strong> hat Ihnen dieses '.$label.' zugewiesen.'
                    : 'Ihnen wurde dieses '.$label.' zugewiesen.',
            ],
            'done' => $mine
                ? ['erledigt', 'Ihr '.$label.' wurde'.$byActor.' als erledigt markiert. Falls noch etwas fehlt, öffnen Sie das Ticket, stellen Sie es wieder her und schreiben Sie einen Kommentar.']
                : ['erledigt'.($who !== '' ? ' von '.$who : ''), $actorDid.' '.$the.' als erledigt markiert.'],
            'restored' => $mine
                ? ['wiederhergestellt', 'Ihr '.$label.' wurde'.$byActor.' wiederhergestellt und ist wieder offen.']
                : ['wiederhergestellt', $actorDid.' '.$the.' wiederhergestellt. Es ist wieder offen.'],
            default => [$event, ''],
        };

        return self::message($system, $id, $title, $subjectEvent)
            ->line(MailText::p($intro))
            ->line(self::facts($system, $id, $model, $title, $submitter))
            ->action('Ticket öffnen', self::url($system, $id));
    }

    /** Empty message with subject "[IT #123] PC-Probleme – <event>" and the team signature. */
    public static function message(string $system, $id, ?string $title, string $event): MailMessage
    {
        $subject = '['.$system.' #'.$id.'] '.(trim((string) $title) !== '' ? trim((string) $title).' – ' : '').$event;

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Guten Tag,')
            ->salutation(self::salutation($system));
    }
}
