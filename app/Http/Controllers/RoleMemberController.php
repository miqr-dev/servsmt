<?php

namespace App\Http\Controllers;

use App\Support\RoleMembers;
use App\User;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;

/**
 * Add / remove members of the roles in RoleMembers::MANAGED from the
 * Rollen & Berechtigungen > Handwerk / Korso pages. Super_Admin only
 * (role:Super_Admin route group + route_access).
 */
class RoleMemberController extends Controller
{
    public function store(Request $request, string $role)
    {
        abort_unless(in_array($role, RoleMembers::MANAGED, true), 404);
        $data = $request->validate(['user_id' => ['required', 'integer', 'exists:users,id']]);

        $user = User::findOrFail($data['user_id']);
        $user->assignRole($role);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return back()->with('success', "{$this->name($user)} hat jetzt die Rolle {$role}.");
    }

    public function destroy(string $role, $userId)
    {
        abort_unless(in_array($role, RoleMembers::MANAGED, true), 404);

        $user = User::withTrashed()->findOrFail($userId);
        $user->removeRole($role);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        if (in_array($role, \App\Support\KorsoAssignments::ROLES, true)) {
            \App\Support\KorsoAssignments::release($user->id, "{$role} entfernt (Rollen & Berechtigungen)");
        }

        return back()->with('success', "Rolle {$role} von {$this->name($user)} entfernt.");
    }

    private function name(User $user): string
    {
        return trim($user->vorname.' '.$user->name) ?: (string) $user->username;
    }
}
