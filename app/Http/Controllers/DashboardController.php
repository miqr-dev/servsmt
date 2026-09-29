<?php

namespace App\Http\Controllers;

use App\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * The one landing page for every user (route "/").
 *
 * Built up role by role: each role adds its own boxes here as an extra prop,
 * the page (resources/js/pages/Home.vue) shows a box only when its prop is
 * present. Roles are additive, Verwaltung is the base every employee has.
 *
 * - Verwaltung: greeting + the user's own email forwardings (from or to them)
 *   + shortcut to a new Email Weiterleitung ticket.
 */
class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $props = [];

        if ($user->hasRole('Verwaltung')) {
            $props['myForwardings'] = $this->myForwardings($user);
        }

        return Inertia::render('Home', $props);
    }

    /**
     * Email forwardings where the user is the sender (forward_from) or the
     * receiver (forward_on): current and upcoming ones, not yet removed.
     */
    private function myForwardings($user): array
    {
        $today = Carbon::today();

        return Ticket::where('problem_type', 'Email Weiterleitung')
            ->where(function ($q) use ($user) {
                $q->where('forward_from', $user->id)->orWhere('forward_on', $user->id);
            })
            ->whereNull('forward_removed_at')
            ->whereNotNull('forward_required_at')
            ->whereNotNull('forward_to_at')
            ->whereDate('forward_to_at', '>=', $today)
            ->with(['forwardFromUser', 'forwardOnUser'])
            ->orderBy('forward_required_at')
            ->get()
            ->map(function (Ticket $t) use ($user, $today) {
                return [
                    'id' => $t->id,
                    'direction' => (int) $t->forward_from === (int) $user->id ? 'out' : 'in',
                    'from' => $this->personName($t->forwardFromUser),
                    'to' => $this->personName($t->forwardOnUser),
                    'start' => $t->forward_required_at->toDateString(),
                    'end' => $t->forward_to_at->toDateString(),
                    'active' => $t->forward_required_at->startOfDay()->lte($today),
                ];
            })
            ->values()
            ->all();
    }

    private function personName($u): string
    {
        if (! $u) {
            return 'Unbekannt';
        }
        $name = trim(($u->vorname ?? '').' '.($u->name ?? ''));

        return $u->trashed() ? $name.' (ausgeschieden)' : $name;
    }
}
