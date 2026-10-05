<?php

namespace App\Http\Controllers;

use App\Support\RoleMembers;
use Inertia\Inertia;

/**
 * Rollen & Berechtigungen > Korso (/korso-zustaendigkeiten), Super_Admin only.
 * Replaces hardcoded user ids in the Korso code (2026-10-02):
 * - Korso_verwaltung: notified when a submitter restores an unassigned Korso
 *   ticket (was user 39). Notification only, no rights.
 * - Korso_Admin: the Korso admin rights (members managed here too).
 * - Printmarketing: may open Printmarketing Verwaltung (was users 1 + 312).
 */
class KorsoResponsibilityController extends Controller
{
    public function index()
    {
        return Inertia::render('Roles/KorsoResponsibilities', [
            'korsoVerwaltung' => RoleMembers::of('Korso_verwaltung'),
            'korsoAdmins' => RoleMembers::of('Korso_Admin'),
            'printmarketing' => RoleMembers::of('Printmarketing'),
            'users' => RoleMembers::allUsers(),
        ]);
    }
}
