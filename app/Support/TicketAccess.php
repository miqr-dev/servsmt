<?php

namespace App\Support;

/**
 * "Owner OR staff" access for the ticket detail pages and the restore
 * actions (2026-09-28). config/route_access.php handles plain role rules;
 * these pages are also opened by the person who submitted the ticket
 * ("Meine Tickets" links there), which a role alone can't express.
 *
 * Owner = the ticket's `submitter`. For Korso tickets submitted "als
 * Sekretariat" the members of that Sekretariat group count as owners too.
 * Super_Admin always passes (App\User::hasRole).
 */
class TicketAccess
{
    public const IT_STAFF = ['Super_Admin', 'admin'];
    public const KORSO_STAFF = ['Korso_ma', 'Korso_Admin'];
    public const HANDWERK_STAFF = ['handwerk_admin', 'handwerk', 'Sekretariat'];

    public static function authorize($ticket, array $staffRoles): void
    {
        $user = auth()->user();
        abort_unless($user, 401);

        if ($user->hasAnyRole($staffRoles) || (int) $ticket->submitter === (int) $user->id) {
            return;
        }

        // Handwerk-Zuständigkeiten: users who see a city's Handwerk tickets in
        // Meine Tickets may open them (App\Support\HandwerkResponsibility).
        if ($ticket instanceof \App\Handwerk && HandwerkResponsibility::canView($user, $ticket->submitter_standort)) {
            return;
        }

        if (! empty($ticket->sek_group_id) && method_exists($ticket, 'sekGroup')) {
            $group = $ticket->sekGroup;
            if ($group && $group->users()->where('users.id', $user->id)->exists()) {
                return;
            }
        }

        abort(403, 'Keine Berechtigung für dieses Ticket.');
    }
}
