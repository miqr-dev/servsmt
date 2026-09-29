<?php

namespace App\Support;

use Illuminate\Support\Collection;
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
        try {
            $notifiable->notify($notification);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public static function send($notifiables, $notification): void
    {
        $list = $notifiables instanceof Collection
            ? $notifiables
            : collect(is_array($notifiables) ? $notifiables : [$notifiables]);

        $list = $list->filter(function ($n) {
            return $n !== null && ! self::isRemoved($n);
        })->values();

        if ($list->isEmpty()) {
            return;
        }

        // A mail problem (SMTP down, certificate error, ...) must not turn an
        // already-saved ticket/comment into an error page - the user would
        // retry and create duplicates. Log it and carry on.
        try {
            Notification::send($list, $notification);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
