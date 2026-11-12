<?php

namespace App\Http\Controllers;

use App\Ticket;
use App\Support\TicketWatchers;
use Illuminate\Http\Request;

/**
 * Beteiligte of an IT ticket, changed on the ticket page (2026-11-12).
 * Only the creator may add or remove them - not the Beteiligte, not IT
 * (decision 2026-11-12). See App\Support\TicketWatchers.
 */
class TicketWatcherController extends Controller
{
    /** POST /ticket/{ticket}/watchers  {user_ids: [5, 12]} -> {watchers, added} */
    public function update(Request $request, $ticket)
    {
        $model = Ticket::withTrashed()->findOrFail((int) $ticket);
        abort_unless(
            (int) $model->submitter === (int) auth()->id(),
            403,
            'Nur der Ersteller des Tickets kann Beteiligte hinzufügen oder entfernen.'
        );

        $request->validate([
            'user_ids' => 'present|array|max:'.TicketWatchers::MAX,
            'user_ids.*' => 'integer',
        ], [
            'user_ids.max' => 'Höchstens '.TicketWatchers::MAX.' Beteiligte pro Ticket.',
        ]);

        $added = TicketWatchers::sync($model, $request->input('user_ids', []), (int) auth()->id());

        return response()->json([
            'watchers' => TicketWatchers::forPage($model),
            'added' => count($added),
        ]);
    }
}
