<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Finds database notifications whose JSON `data` has a given top-level "id"
 * (ticket / handwerk / korso / comment id - every *Notification::toDatabase()
 * in this app stores the related record's id under that key).
 *
 * Replaces the old `->where('data->id', $id)` lookups (2026-09-28). Those
 * compile to MySQL `json_extract(data, '$."id"')`, which evaluates EVERY row
 * the query scans, and MySQL aborts the whole query with error 3141
 * ("Invalid JSON text ... Invalid encoding in string") as soon as a single
 * notification row holds bytes that aren't valid UTF-8 - e.g. "Erledigt" on
 * an Email-Weiterleitung ticket failed because one of an admin's older
 * notifications was malformed, unrelated to that ticket.
 *
 * Here the database only does a plain-text LIKE pre-filter (never parses
 * JSON, so a malformed row can't break it), and the exact match happens in
 * PHP on the decoded `data` array. A row whose JSON can't be decoded simply
 * doesn't match instead of failing the request. Same semantics as before
 * otherwise: matches on "id" only, in the relation's own order (the
 * notifications() relations are ordered by created_at desc).
 */
class NotificationLookup
{
    /**
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation  $query
     * @param  int|string  $id
     */
    public static function byDataId($query, $id): Collection
    {
        $id = trim((string) $id);
        if ($id === '' || ! ctype_digit($id)) {
            return collect();
        }

        return $query
            ->where(function ($q) use ($id) {
                // json_encode writes the id either as a number ("id":123) or,
                // when it came straight from a request, a string ("id":"123").
                $q->where('data', 'like', '%"id":' . $id . '%')
                    ->orWhere('data', 'like', '%"id":"' . $id . '"%');
            })
            ->get()
            ->filter(function ($notification) use ($id) {
                $data = $notification->data;

                return is_array($data) && isset($data['id']) && (string) $data['id'] === $id;
            })
            ->values();
    }

    /**
     * Every notification that belongs to ONE ticket of ONE system (2026-10-01).
     *
     * byDataId() matched any notification whose data.id equals the number, so
     * IT ticket 5, Handwerk 5 and Korso 5 (and their comments) cleared each
     * other, and Korso notifications (stored under "korso_id") never matched.
     * This also checks the notification class / comment type:
     *
     *   ticket   : TicketNotification (id), ReminderNotification (ticket_id),
     *              CommentNotification with type "ticket" (or no type - old rows)
     *   handwerk : HandwerkNotification (id), CommentNotification type "handwerk"
     *   korso    : KorsoNotification (korso_id), CommentNotification type "korso"
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation  $query
     */
    public static function forRecord($query, string $kind, $id): Collection
    {
        $id = trim((string) $id);
        if ($id === '' || ! ctype_digit($id)) {
            return collect();
        }

        $keys = ['id', 'korso_id', 'ticket_id'];

        return $query
            ->where(function ($q) use ($id, $keys) {
                foreach ($keys as $k) {
                    $q->orWhere('data', 'like', '%"' . $k . '":' . $id . '%')
                        ->orWhere('data', 'like', '%"' . $k . '":"' . $id . '"%');
                }
            })
            ->get()
            ->filter(function ($n) use ($id, $kind) {
                $data = $n->data;
                if (! is_array($data)) {
                    return false;
                }
                $is = fn ($key) => isset($data[$key]) && (string) $data[$key] === $id;
                $class = class_basename($n->type);

                switch ($class) {
                    case 'TicketNotification':
                        return $kind === 'ticket' && $is('id');
                    case 'ReminderNotification':
                        return $kind === 'ticket' && $is('ticket_id');
                    case 'HandwerkNotification':
                        return $kind === 'handwerk' && $is('id');
                    case 'KorsoNotification':
                        return $kind === 'korso' && $is('korso_id');
                    case 'CommentNotification':
                        return $is('id') && strtolower($data['type'] ?? 'ticket') === $kind;
                    default:
                        return false;
                }
            })
            ->values();
    }

    /** Mark all of a user's unread notifications for this ticket as read. */
    public static function markReadFor($user, string $kind, $id): void
    {
        if (! $user) {
            return;
        }
        self::forRecord($user->unreadNotifications(), $kind, $id)
            ->each(fn ($n) => $n->markAsRead());
    }
}
