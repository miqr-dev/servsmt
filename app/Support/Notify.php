<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;

/**
 * Null- and removed-user-safe wrapper around Notification::send().
 *
 * Users that disappear from Active Directory are soft-deleted by the LDAP
 * import (not deleted), so old tickets keep showing who created / handled
 * them - every User relation on tickets, Korso, Handwerk, comments etc. uses
 * ->withTrashed() for that. Those relations can therefore hand back a
 * removed user, and plain User::find() hands back null for them. Before
 * this, Notification::send(null, ...) threw an error (e.g. commenting on,
 * assigning or closing a ticket whose creator had left), and a removed user
 * would still have been mailed.
 *
 * Notify::send() drops null and removed users and only sends if someone is
 * left. Every notification class also returns no channels for a removed
 * user in via(), which covers the direct $user->notify(...) calls.
 */
class Notify
{
    /** True for a soft-deleted (removed from AD) user. */
    public static function isRemoved($notifiable): bool
    {
        return is_object($notifiable)
            && method_exists($notifiable, 'trashed')
            && $notifiable->trashed();
    }

    /**
     * Same safety for single ->notify() calls (users or Notification::route()
     * recipients): a mail failure is logged, never shown to the user.
     */
    public static function one($notifiable, $notification): void
    {
        if ($notifiable === null || self::isRemoved($notifiable)) {
            return;
        }
        self::deliver($notifiable, $notification);
    }

    public static function send($notifiables, $notification): void
    {
        $list = $notifiables instanceof Collection
            ? $notifiables
            : collect(is_array($notifiables) ? $notifiables : [$notifiables]);

        $list->filter(function ($n) {
            return $n !== null && ! self::isRemoved($n);
        })->each(function ($n) use ($notification) {
            self::deliver($n, $notification);
        });
    }

    /**
     * Deliver to ONE recipient, channel by channel, in-app ("database") first.
     *
     * Before (2026-10-01): Notification::send($all, ...) sent mail before
     * database, recipient after recipient, inside one try/catch. One failing
     * mail (e.g. the SMTP certificate error on the dev server) aborted
     * everything after it - no in-app notification for that person and
     * nothing at all for the remaining recipients. Now every channel of every
     * recipient is tried on its own and failures are only logged.
     */
    private static function deliver($notifiable, $notification): void
    {
        try {
            $channels = (array) $notification->via($notifiable);
        } catch (\Throwable $e) {
            report($e);

            return;
        }

        // On-demand recipients (Notification::route('mail', ...)) only have
        // the channels they were given a route for.
        if ($notifiable instanceof AnonymousNotifiable) {
            $channels = array_values(array_filter($channels, function ($c) use ($notifiable) {
                return $notifiable->routeNotificationFor($c) !== null;
            }));
        }

        usort($channels, function ($a, $b) {
            return ($a === 'database' ? 0 : 1) <=> ($b === 'database' ? 0 : 1);
        });

        foreach ($channels as $channel) {
            try {
                Notification::sendNow($notifiable, $notification, [$channel]);
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
