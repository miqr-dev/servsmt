<?php

namespace App\Http\Controllers;

use App\Support\RoleOverview;
use Inertia\Inertia;

/**
 * Rollen & Berechtigungen > Übersicht (/rollen-uebersicht, 2026-10-11):
 * diagram of all roles and who can do what (App\Support\RoleOverview,
 * Roles/Overview.vue). Super_Admin only: role:Super_Admin route group +
 * config/route_access.php + the check below.
 */
class RoleOverviewController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        return Inertia::render('Roles/Overview', RoleOverview::build());
    }
}
