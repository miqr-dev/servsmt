<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Local-development-only convenience.
 *
 * In production this app is authenticated via IIS Windows-Integrated Auth +
 * LDAP (see WindowsAuthenticate, which runs immediately before this in the
 * 'web' middleware group). On a machine with no real Active Directory
 * reachable (e.g. Laravel Herd on a dev laptop), nobody is ever
 * authenticated, and code that assumes Auth::user() is populated crashes.
 *
 * When config('app.local_dev_auth_bypass') is explicitly true and no user
 * was authenticated by the real LDAP flow, this logs in a local test user
 * (config('app.local_dev_auth_user_id'), or the first user in the table if
 * not set) purely for local development convenience.
 *
 * Safety: this is gated by a dedicated LOCAL_DEV_AUTH_BYPASS flag rather
 * than APP_ENV/environment name, because APP_ENV is not a reliable signal
 * of "this is a local dev machine" in this app's actual deployments. If the
 * flag is missing or not exactly true, this middleware does nothing and the
 * real LDAP-authenticated user (or lack of one) is left untouched.
 */
class LocalDevAuthBypass
{
    public function handle($request, Closure $next)
    {
        if (config('app.local_dev_auth_bypass') === true && ! Auth::check()) {
            $userId = config('app.local_dev_auth_user_id');

            $user = $userId
                ? User::find($userId)
                : User::query()->orderBy('id')->first();

            if ($user) {
                Auth::login($user);
            }
        }

        return $next($request);
    }
}
