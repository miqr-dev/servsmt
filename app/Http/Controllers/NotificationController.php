<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

/**
 * Small endpoints for the unified notifications (Dashboard "Neu für dich",
 * sidebar counter). Tickets mark themselves read when opened; these cover
 * "Alle gelesen" and the non-ticket items (e.g. Kündigungen).
 */
class NotificationController extends Controller
{
    /** Polled every minute by the sidebar (counter, tab title, unread dots). */
    public function summary()
    {
        return response()->json(\App\Support\NotificationFeed::summary(Auth::user()));
    }

    public function readAll()
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);

        return back();
    }

    /** Mark one notification read, then go where it points to. */
    public function open(string $id)
    {
        $n = Auth::user()->notifications()->where('id', $id)->firstOrFail();
        $n->markAsRead();
        $target = (class_basename($n->type) === 'TerminationDeletedNotification')
            ? ((($n->data['status'] ?? '') === 'inactive') ? '/terminations' : '/terminations/history')
            : '/';

        return redirect($target);
    }
}
