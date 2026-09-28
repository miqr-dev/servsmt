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
}
