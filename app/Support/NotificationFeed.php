<?php

namespace App\Support;

use App\Handwerk;
use App\Korso;
use App\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * One feed of a user's unread in-app notifications - for ALL systems at once
 * (IT, Handwerk, Korso, comments, reminders, Kündigungen) - grouped per
 * record, so ten comments on one ticket are one line (2026-10-01).
 *
 * Used by:
 * - HandleInertiaRequests (shared `notifications`: total + unread keys, for
 *   the sidebar counter, tab title and unread dots in ticket lists),
 * - DashboardController ("Neu für dich" box: the grouped list).
 *
 * Opening a ticket marks its notifications read (NotificationLookup::markReadFor),
 * so a group disappears by itself once it was looked at.
 */
class NotificationFeed
{
    /**
     * Map one notification to [key, kind, id] - key like "ticket:5".
     * kind: ticket | handwerk | korso | other
     */
    public static function target($n): array
    {
        $d = is_array($n->data) ? $n->data : [];
        switch (class_basename($n->type)) {
            case 'TicketNotification':
                return ['ticket', $d['id'] ?? null];
            case 'ReminderNotification':
                return ['ticket', $d['ticket_id'] ?? null];
            case 'HandwerkNotification':
                return ['handwerk', $d['id'] ?? null];
            case 'KorsoNotification':
                return ['korso', $d['korso_id'] ?? null];
            case 'CommentNotification':
                $type = strtolower($d['type'] ?? 'ticket');

                return [in_array($type, ['ticket', 'handwerk', 'korso'], true) ? $type : 'ticket', $d['id'] ?? null];
            default:
                return ['other', null];
        }
    }

    /** Small payload for every page: total + keys of records with unread activity. */
    public static function summary($user): array
    {
        $keys = [];
        $total = 0;
        foreach ($user->unreadNotifications()->get(['id', 'type', 'data']) as $n) {
            [$kind, $id] = self::target($n);
            $key = ($kind === 'other' || $id === null) ? 'other:' . $n->id : $kind . ':' . $id;
            $keys[$key] = true;
            $total++;
        }

        return [
            // number of LINES (records), not raw notifications
            'count' => count($keys),
            'total' => $total,
            'keys' => array_keys($keys),
        ];
    }

    /** Grouped list for the Dashboard box, newest activity first. */
    public static function groups($user, int $limit = 50): array
    {
        $groups = [];
        foreach ($user->unreadNotifications()->get() as $n) {
            [$kind, $id] = self::target($n);
            $d = is_array($n->data) ? $n->data : [];
            $key = ($kind === 'other' || $id === null) ? 'other:' . $n->id : $kind . ':' . $id;

            if (! isset($groups[$key])) {
                $groups[$key] = [
                    'key' => $key,
                    'kind' => $kind,
                    'id' => $id !== null ? (int) $id : null,
                    'title' => null,
                    'events' => [],
                    'comments' => 0,
                    'latest' => $n->created_at,
                    'url' => null,
                    // non-ticket items: link that marks the notification read first
                    'open' => $kind === 'other' ? '/notifications/' . $n->id . '/open' : null,
                ];
            }
            $g = &$groups[$key];
            if ($n->created_at && $n->created_at->gt($g['latest'])) {
                $g['latest'] = $n->created_at;
            }

            switch (class_basename($n->type)) {
                case 'CommentNotification':
                    $g['comments']++;
                    break;
                case 'ReminderNotification':
                    $g['events'][] = 'Erinnerung';
                    $g['title'] = $g['title'] ?? ($d['ticket_title'] ?? null);
                    break;
                case 'TerminationDeletedNotification':
                    $g['events'][] = $d['title'] ?? 'Kündigung';
                    $g['title'] = trim(($d['name'] ?? '') . ' · ' . ($d['location'] ?? ''), ' ·');
                    $g['url'] = ($d['status'] ?? '') === 'inactive' ? '/terminations' : '/terminations/history';
                    break;
                default:
                    $g['events'][] = $d['title'] ?? 'Benachrichtigung';
                    $g['title'] = $g['title'] ?? ($d['problem_type'] ?? null);
            }
            unset($g);
        }

        // Fill missing titles (comment-only groups) from the records, in 3 queries.
        foreach (['ticket' => Ticket::class, 'handwerk' => Handwerk::class, 'korso' => Korso::class] as $kind => $model) {
            $ids = collect($groups)->where('kind', $kind)->whereNull('title')->pluck('id')->filter()->unique()->all();
            if ($ids) {
                $titles = $model::withTrashed()->whereIn('id', $ids)->pluck('problem_type', 'id');
                foreach ($groups as &$g) {
                    if ($g['kind'] === $kind && $g['title'] === null) {
                        $g['title'] = $titles[$g['id']] ?? null;
                    }
                }
                unset($g);
            }
        }

        $urls = ['ticket' => '/ticket/', 'handwerk' => '/handwerk/', 'korso' => '/korso/'];

        return collect($groups)
            ->map(function ($g) use ($urls) {
                if (isset($urls[$g['kind']]) && $g['id']) {
                    $g['url'] = $urls[$g['kind']] . $g['id'];
                }
                $g['events'] = array_values(array_unique($g['events']));
                $g['latest'] = $g['latest'] instanceof Carbon ? $g['latest']->toIso8601String() : null;

                return $g;
            })
            ->sortByDesc('latest')
            ->take($limit)
            ->values()
            ->all();
    }
}
