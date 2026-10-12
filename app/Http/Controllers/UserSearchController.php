<?php

namespace App\Http\Controllers;

use App\AdOu;
use App\Support\AdStates;
use App\User;
use Inertia\Inertia;

/**
 * Benutzer-Suche (/benutzer-suche, Super_Admin only, 2026-10-12):
 * find users by Bundesland, Stadt, Adresse, Abteilung, Position, Rolle and
 * free text; counts per filter value, grouping, CSV export, copy e-mails
 * (Users/Search.vue - filtering runs in the browser, a few hundred users).
 *
 * Bundesland: the user's AD attribute "st" (users.bundesland) when it is a
 * real state name, else the Bundesland of the user's Standort
 * (Einstellungen > Standorte & Adressen / config ad_inventory.states),
 * else whatever AD has.
 */
class UserSearchController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        // Standort name -> Bundesland, from the AD Standorte (app setting first)
        $stateByCity = [];
        foreach (AdOu::where('type', 'standort')->get(['id', 'label', 'name', 'state']) as $ou) {
            $state = AdStates::of($ou);
            if ($state) {
                $stateByCity[AdStates::norm($ou->label ?: $ou->name)] = $state;
            }
        }
        $states = collect(AdStates::OPTIONS)->mapWithKeys(fn ($s) => [AdStates::norm($s) => $s])->all();

        $users = User::withTrashed()
            ->with('roles')
            ->orderBy('name')->orderBy('vorname')
            ->get()
            ->map(function (User $u) use ($stateByCity, $states) {
                $ort = trim((string) $u->ort);
                $adState = $states[AdStates::norm($u->bundesland)] ?? null;

                return [
                    'id' => $u->id,
                    'name' => trim($u->vorname.' '.$u->name) ?: (string) $u->username,
                    'nachname' => (string) $u->name,
                    'vorname' => (string) $u->vorname,
                    'username' => (string) $u->username,
                    'email' => (string) $u->email,
                    'tel' => (string) $u->tel,
                    'mobil' => (string) $u->mobil,
                    'ort' => $ort,
                    'strasse' => trim((string) $u->getAttribute('straße')),
                    'plz' => (string) $u->plz,
                    'bundesland' => $adState ?? $stateByCity[AdStates::norm($ort)] ?? AdStates::default($ort) ?? trim((string) $u->bundesland),
                    'abteilung' => trim((string) $u->abteilung),
                    'position' => trim((string) $u->position),
                    'office' => trim((string) $u->office),
                    'lastlogin' => $u->lastlogin ? (string) $u->lastlogin : null,
                    'roles' => $u->roles->pluck('name')->sort()->values()->all(),
                    'removed' => $u->trashed(),
                ];
            })
            ->values();

        return Inertia::render('Users/Search', [
            'users' => $users,
        ]);
    }
}
