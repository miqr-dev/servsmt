<?php

namespace App\Http\Controllers;

use App\Support\EmployeePassword;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Einstellungen > Neuer Mitarbeiter (/settings/neuer-mitarbeiter,
 * Super_Admin via route_access 'settings/*', 2026-10-11): the default
 * Windows password sent in the "Neuer Mitarbeiter" mail.
 */
class NewEmployeeSettingsController extends Controller
{
    public function index()
    {
        $setting = EmployeePassword::setting();
        $editor = $setting?->editor;

        return Inertia::render('Settings/NewEmployee', [
            'password' => EmployeePassword::value(),
            'changedAt' => $setting?->updated_at?->toIso8601String(),
            'changedBy' => $editor ? (trim($editor->vorname.' '.$editor->name) ?: $editor->username) : null,
            'outdated' => EmployeePassword::isOutdated(),
            'suggestion' => EmployeePassword::suggestion(),
            'year' => now()->year,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'max:100'],
        ], [], ['password' => 'Passwort']);

        EmployeePassword::set(trim($data['password']), auth()->id());

        return back()->with('success', 'Standard-Passwort gespeichert.');
    }
}
