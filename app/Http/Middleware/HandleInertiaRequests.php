<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user ? [
                    ...$user->toArray(),
                    // Effective roles - a Super_Admin gets EVERY role name, so all
                    // frontend checks (sidebar, roles.includes('Korso_Admin'), …)
                    // treat them as holding every role. See App\User::hasRole().
                    'roles' => $user->isSuperAdmin()
                        ? \Spatie\Permission\Models\Role::query()->pluck('name')->unique()->values()
                        : $user->getRoleNames(),
                    // Roles actually assigned (for display, e.g. a profile page).
                    'assignedRoles' => $user->getRoleNames(),
                    'isSuperAdmin' => $user->isSuperAdmin(),
                ] : null,
            ],
            // Unread in-app notifications, all systems (App\Support\NotificationFeed):
            // count of records with news + their keys ("ticket:5", "korso:12", …)
            // for the sidebar counter, the tab title and unread dots in lists.
            'notifications' => fn () => $user ? \App\Support\NotificationFeed::summary($user) : null,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'status' => fn () => $request->session()->get('status'),
                // Older controllers (Role/Permission/User CRUD) flash a
                // ['message' => ..., 'alert-type' => 'success'|'danger'|...]
                // array via ->with($sucMsg) instead of ->with('success', ...).
                // Share both keys rather than touching every controller.
                'message' => fn () => $request->session()->get('message'),
                'alertType' => fn () => $request->session()->get('alert-type'),
                // TicketController@store_participant flashes row-level Excel
                // import failures as an array under 'import_errors'.
                'importErrors' => fn () => $request->session()->get('import_errors'),
            ],
        ];
    }
}
