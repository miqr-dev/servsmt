<?php

namespace App\Http\Controllers;

use App\Handwerk;
use App\License;
use App\Termination;
use App\NewsBar;
use App\User;
use App\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
 *   + shortcut to a new Email Weiterleitung ticket
 *   + small "Neues Ticket" box on the right (IT / Korso / Handwerk).
 * - Sekretariat: instead of the personal list, a table of all active
 *   forwardings at their Standort (Berlin split by address) + their own,
 *   and below it the open Handwerk tasks of their city.
 * - HR: Kündigungen. Super_Admin: + Lizenzen + all email forwardings
 *   (components/dashboard/AdminBoxes.vue). The old /dashboard page is gone.
 */
class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $props = [
            // "Neu für dich": unread notifications of all systems, grouped per ticket.
            'newForYou' => \App\Support\NotificationFeed::groups($user),
            // News bar (Einstellungen > Newsbar) - shown to everyone next to the
            // greeting. Moved here from the IT ticket page (2026-09-29).
            'newsBar' => $this->newsBar(),
        ];

        if ($user->hasRole('Verwaltung')) {
            $props['createShortcuts'] = true;
            $props['employeeLookup'] = true; // "Mitarbeiter Info" box
        }

        // Email forwardings: Sekretariat gets the Standort table INSTEAD of the
        // personal list (their own forwardings are included in it).
        if ($user->hasRole('Sekretariat')) {
            $props['standortForwardings'] = $this->standortForwardings($user);
            $props['standortLabel'] = $user->ort === 'Berlin' && $user->straße
                ? 'Berlin, '.$user->straße
                : (string) $user->ort;
        } elseif ($user->hasRole('Verwaltung')) {
            $props['myForwardings'] = $this->myForwardings($user);
        }

        // Sekretariat: open Handwerk tasks of their city (under the forwardings).
        if ($user->hasRole('Sekretariat') && $user->ort) {
            $props['cityHandwerks'] = $this->cityHandwerks($user->ort);
            $props['handwerkCity'] = $user->ort;
        }

        // HR: Kündigungen (Super_Admin counts as HR too).
        if ($user->hasRole('HR')) {
            $props['terminations'] = Termination::orderBy('exit', 'ASC')->get();
        }

        // Super_Admin only: Lizenzen + all email forwardings (active / history).
        // Moved here from the former /dashboard page (LicenseController@index).
        if ($user->isSuperAdmin()) {
            $props['licenses'] = License::orderByRaw('CASE WHEN valid IS NULL THEN 0 ELSE 1 END DESC')
                ->orderBy('valid', 'ASC')
                ->get();

            // Only the columns/relations the tables show (was: every ticket column
            // + 5 full user models per row, for every forwarding ever).
            $userCols = 'id,name,vorname,username';
            $forwardings = Ticket::withTrashed()
                ->select([
                    'id', 'submitter', 'problem_type', 'forward_from', 'forward_on', 'forward_removed_by',
                    'forward_required_at', 'forward_to_at', 'forward_removed_at', 'done_by', 'created_at', 'deleted_at',
                ])
                ->with([
                    'subUser:' . $userCols,
                    'forwardOnUser:' . $userCols,
                    'forwardFromUser:' . $userCols,
                    'forwardRemovedByUser:' . $userCols,
                ])
                ->where('problem_type', 'Email Weiterleitung')
                ->orderBy('forward_required_at', 'asc')
                ->orderByDesc('created_at')
                ->get();
            $props['activeEmailForwardingTickets'] = $forwardings
                ->filter(fn ($t) => ! empty($t->forward_required_at) && ! empty($t->forward_to_at) && empty($t->forward_removed_at))
                ->values();
            $props['historyEmailForwardingTickets'] = $forwardings
                ->filter(fn ($t) => ! empty($t->forward_removed_at))
                ->values();
        }

        return Inertia::render('Home', $props);
    }

    /**
     * Sekretariat: all currently ACTIVE forwardings of people at the user's
     * Standort (forwardFromUser.ort; Berlin also the same straße) - the rule of
     * the old landing page - plus the user's own current/upcoming ones.
     */
    private function standortForwardings($user): array
    {
        $today = Carbon::today();

        $standort = Ticket::where('problem_type', 'Email Weiterleitung')
            ->whereNull('forward_removed_at')
            ->whereNotNull('forward_required_at')
            ->whereNotNull('forward_to_at')
            ->whereDate('forward_required_at', '<=', $today)
            ->whereDate('forward_to_at', '>=', $today)
            ->whereHas('forwardFromUser', function ($q) use ($user) {
                $q->where('ort', $user->ort);
                if ($user->ort === 'Berlin') {
                    $q->where('straße', $user->straße);
                }
            })
            ->with(['forwardFromUser', 'forwardOnUser'])
            ->get()
            ->map(fn (Ticket $t) => $this->forwardingRow($t, $user, $today));

        return collect($this->myForwardings($user))
            ->concat($standort)
            ->unique('id')
            ->sortBy([['start', 'asc'], ['from', 'asc']])
            ->values()
            ->all();
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
            ->map(fn (Ticket $t) => $this->forwardingRow($t, $user, $today))
            ->values()
            ->all();
    }

    /**
     * Open (not soft-deleted = not Erledigt) Handwerk tickets whose
     * submitter_standort is the user's ort - same rule as the city list and
     * the open-tickets PDF (HandwerkController@showCity / @openTicketsPDF).
     */
    private function cityHandwerks(string $city): array
    {
        $tickets = Handwerk::where('submitter_standort', $city)
            ->with(['room', 'location', 'subUser'])
            ->orderBy('created_at') // oldest first
            ->get();

        $assignees = User::withTrashed()
            ->whereIn('id', $tickets->pluck('assignedTo')->filter()->unique())
            ->get()
            ->keyBy('id');

        return $tickets->map(function (Handwerk $h) use ($assignees) {
            $assignee = $h->assignedTo ? $assignees->get($h->assignedTo) : null;

            return [
                'id' => $h->id,
                'problem_type' => $h->problem_type,
                'room' => $h->room->rname ?? null,
                'address' => $h->location->address ?? null,
                'submitter' => $this->personName($h->subUser),
                'assignee' => $assignee ? $this->personName($assignee) : null,
                'created_at' => optional($h->created_at)->toIso8601String(),
            ];
        })->values()->all();
    }

    private function forwardingRow(Ticket $t, $user, Carbon $today): array
    {
        $isFrom = (int) $t->forward_from === (int) $user->id;
        $isTo = (int) $t->forward_on === (int) $user->id;

        return [
            'id' => $t->id,
            'direction' => $isFrom ? 'out' : 'in',
            'own' => $isFrom || $isTo,
            'from' => $this->personName($t->forwardFromUser),
            'to' => $this->personName($t->forwardOnUser),
            'start' => $t->forward_required_at->toDateString(),
            'end' => $t->forward_to_at->toDateString(),
            'active' => $t->forward_required_at->copy()->startOfDay()->lte($today),
        ];
    }

    /**
     * "Mitarbeiter Info" box (every employee): look up a colleague by first
     * name, last name or username. Like the old contacts "Suche nach Name",
     * but only work data is returned - no private / mobile numbers.
     * Every word of the query must match vorname, name or username.
     */
    public function employeeSearch(Request $request)
    {
        $words = preg_split('/\s+/', trim((string) $request->query('q', '')), -1, PREG_SPLIT_NO_EMPTY);
        $words = array_slice($words, 0, 4);
        if (! $words || mb_strlen(implode('', $words)) < 2) {
            return response()->json([]);
        }

        $users = User::query() // soft-deleted (left AD) users are excluded
            ->where(function ($q) use ($words) {
                foreach ($words as $w) {
                    $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $w).'%';
                    $q->where(function ($q) use ($like) {
                        $q->where('vorname', 'like', $like)
                            ->orWhere('name', 'like', $like)
                            ->orWhere('username', 'like', $like);
                    });
                }
            })
            ->orderBy('name')
            ->orderBy('vorname')
            ->limit(10)
            ->get(['id', 'vorname', 'name', 'username', 'position', 'abteilung', 'straße', 'plz', 'ort', 'tel', 'email']);

        return response()->json($users->map(fn (User $u) => [
            'id' => $u->id,
            'vorname' => $u->vorname,
            'name' => $u->name,
            'username' => $u->username,
            'position' => $u->position,
            'abteilung' => $u->abteilung,
            'street' => $u->straße,
            'plz' => $u->plz,
            'ort' => $u->ort,
            'tel' => $u->tel,
            'email' => $u->email,
        ])->values());
    }

    private function newsBar(): ?string
    {
        $bar = NewsBar::find(1) ?? NewsBar::query()->orderBy('id')->first();
        if (! $bar || $bar->isNewsBar !== 'on' || trim((string) $bar->name) === '') {
            return null;
        }

        return $bar->name;
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
