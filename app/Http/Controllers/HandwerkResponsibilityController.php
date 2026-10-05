<?php

namespace App\Http\Controllers;

use App\HandwerkUserSetting;
use App\Support\HandwerkResponsibility;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

/**
 * Rollen & Berechtigungen > Handwerk (/handwerk-zustaendigkeiten), Super_Admin
 * only (role:Super_Admin route group). See App\Support\HandwerkResponsibility.
 */
class HandwerkResponsibilityController extends Controller
{
    public function index()
    {
        // withTrashed: users soft-deleted by ldap:import (e.g. external
        // craftsmen not in the AD import group) stay visible, marked inactive.
        $settings = HandwerkUserSetting::with(['user' => fn ($q) => $q->withTrashed()->select('id', 'vorname', 'name', 'username', 'ort', 'deleted_at')])->get()
            ->filter(fn ($s) => $s->user)
            ->map(fn ($s) => [
                'user_id' => $s->user_id,
                'name' => trim($s->user->vorname.' '.$s->user->name) ?: $s->user->username,
                'username' => $s->user->username,
                'ort' => $s->user->ort,
                'view_cities' => array_values($s->view_cities ?? []),
                'assign_cities' => array_values($s->assign_cities ?? []),
                'pdf_by_mail' => $s->pdf_by_mail,
                'inactive' => $s->user->deleted_at !== null,
            ])
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();

        $verwaltung = HandwerkResponsibility::verwaltungUsers()
            ->map(fn ($u) => ['id' => $u->id, 'name' => trim($u->vorname.' '.$u->name) ?: $u->username, 'username' => $u->username])
            ->sortBy('name')->values();

        $users = User::orderBy('name')->orderBy('vorname')->get(['id', 'vorname', 'name', 'username', 'ort'])
            ->map(fn ($u) => ['id' => $u->id, 'name' => trim($u->vorname.' '.$u->name) ?: $u->username, 'username' => $u->username, 'ort' => $u->ort]);

        return Inertia::render('Roles/HandwerkResponsibilities', [
            'settings' => $settings,
            'verwaltung' => $verwaltung,
            'verwaltungRole' => HandwerkResponsibility::VERWALTUNG_ROLE,
            'users' => $users,
            'cities' => HandwerkResponsibility::knownCities(),
        ]);
    }

    public function update(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $cities = HandwerkResponsibility::knownCities();
        $data = $request->validate([
            'view_cities' => ['array'],
            'view_cities.*' => ['string', Rule::in($cities)],
            'assign_cities' => ['array'],
            'assign_cities.*' => ['string', Rule::in($cities)],
            'pdf_by_mail' => ['boolean'],
        ]);

        $view = array_values(array_unique($data['view_cities'] ?? []));
        $assign = array_values(array_unique($data['assign_cities'] ?? []));
        $pdf = (bool) ($data['pdf_by_mail'] ?? false);

        if (! $view && ! $assign && ! $pdf) {
            HandwerkUserSetting::where('user_id', $user->id)->delete();

            return back()->with('success', 'Zuständigkeiten entfernt.');
        }

        HandwerkUserSetting::updateOrCreate(['user_id' => $user->id], [
            'view_cities' => $view ?: null,
            'assign_cities' => $assign ?: null,
            'pdf_by_mail' => $pdf,
        ]);

        return back()->with('success', 'Zuständigkeiten gespeichert.');
    }

    public function destroy($userId)
    {
        HandwerkUserSetting::where('user_id', (int) $userId)->delete();

        return back()->with('success', 'Zuständigkeiten entfernt.');
    }
}
