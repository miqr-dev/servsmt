<?php

namespace App\Support;

use App\Notifications\TicketNotification;
use App\Ticket;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Beteiligte (watchers) of IT tickets (2026-11-12).
 *
 * Request (Berlin): many requests concern several colleagues, not only the
 * person who writes the ticket. The creator can add colleagues in the
 * creation form (SubmitterCard.vue) and later on the ticket page
 * (Tickets/Show.vue, TicketWatcherController).
 *
 * Beteiligte act exactly like the creator:
 *  - open, comment on and restore the ticket (TicketAccess::authorize);
 *  - see it under Meine Tickets / Erledigte (Ticket::scopeMine), marked
 *    "Mitverfolgt";
 *  - get the same notifications: comments (CommentController::notifyAbout),
 *    Erledigt (TicketController@destroy), Neuer Mitarbeiter
 *    (TicketController@mitarbeitersave).
 * Not allowed for them: adding / removing Beteiligte (creator only) and
 * deleting the ticket (Meine Tickets › Löschen: creator or IT staff).
 *
 * IT tickets only, not Handwerk / Korso (decision 2026-11-12).
 * Removed (soft-deleted) users drop out by themselves: the User queries here
 * leave them out.
 */
class TicketWatchers
{
    public const TABLE = 'ticket_watchers';

    /** most Beteiligte per ticket */
    public const MAX = 50;

    /**
     * Every active user for the picker (MultiCombobox options), without
     * $exceptId (the creator, on the ticket page):
     * [{value: "5", label: "Max Mustermann", hint: "Berlin · mmustermann"}].
     */
    public static function options(?int $exceptId = null): array
    {
        return User::query()
            ->select('id', 'vorname', 'name', 'username', 'ort')
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->orderBy('name')->orderBy('vorname')
            ->get()
            ->map(fn ($u) => [
                'value' => (string) $u->id,
                'label' => TicketMail::person($u),
                'hint' => trim(implode(' · ', array_filter([$u->ort, $u->username]))),
            ])
            ->filter(fn ($o) => $o['label'] !== '')
            ->values()
            ->all();
    }

    /** Active Beteiligte of a ticket (User models), for notifications. */
    public static function users(Ticket $ticket): Collection
    {
        return $ticket->watchers()->get();
    }

    /** Beteiligte for the ticket page: [{id, name, ort}]. */
    public static function forPage(Ticket $ticket): array
    {
        return self::users($ticket)
            ->sortBy(fn ($u) => TicketMail::person($u))
            ->map(fn ($u) => ['id' => (int) $u->id, 'name' => TicketMail::person($u), 'ort' => $u->ort])
            ->values()
            ->all();
    }

    public static function isWatcher(Ticket $ticket, $userId): bool
    {
        return (int) $userId > 0
            && DB::table(self::TABLE)->where('ticket_id', $ticket->getKey())->where('user_id', (int) $userId)->exists();
    }

    /** ids of the tickets a user is Beteiligte(r) of (Meine Tickets marker) */
    public static function ticketIdsFor($userId): array
    {
        return DB::table(self::TABLE)->where('user_id', (int) $userId)->pluck('ticket_id')->map(fn ($id) => (int) $id)->all();
    }

    /** Requested ids without duplicates, the creator, unknown and removed users. */
    public static function clean($ids, int $submitterId): array
    {
        $ids = collect((array) $ids)
            ->filter(fn ($v) => is_numeric($v))
            ->map(fn ($v) => (int) $v)
            ->reject(fn ($id) => $id <= 0 || $id === $submitterId)
            ->unique()
            ->take(self::MAX)
            ->values();
        if ($ids->isEmpty()) {
            return [];
        }

        return User::whereIn('id', $ids->all())->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
    }

    /**
     * Sets the Beteiligte of a ticket to exactly $ids. Newly added people get
     * a mail and an in-app entry ("Hinzugefügt"); removed ones get nothing.
     *
     * @return int[] ids that were added
     */
    public static function sync(Ticket $ticket, $ids, ?int $addedBy): array
    {
        $ids = self::clean($ids, (int) $ticket->submitter);
        $current = DB::table(self::TABLE)->where('ticket_id', $ticket->getKey())
            ->pluck('user_id')->map(fn ($id) => (int) $id)->all();
        $added = array_values(array_diff($ids, $current));
        $removed = array_values(array_diff($current, $ids));

        DB::transaction(function () use ($ticket, $added, $removed, $addedBy) {
            if ($removed) {
                DB::table(self::TABLE)->where('ticket_id', $ticket->getKey())->whereIn('user_id', $removed)->delete();
            }
            if ($added) {
                $now = now();
                DB::table(self::TABLE)->insertOrIgnore(array_map(fn ($id) => [
                    'ticket_id' => $ticket->getKey(),
                    'user_id' => $id,
                    'added_by' => $addedBy,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $added));
            }
        });

        if ($added) {
            Notify::send(User::whereIn('id', $added)->get(), new TicketNotification([
                'title' => 'Hinzugefügt',
                'ticket_id' => $ticket->getKey(),
                'submitter' => optional($ticket->subUser)->username,
                'problem_type' => $ticket->problem_type,
            ]));
        }

        return $added;
    }
}
