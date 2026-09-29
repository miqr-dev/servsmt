<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Enforces config/route_access.php: looks up the matched route's URI (and
 * method) and, if it's listed, requires the user to hold one of the roles.
 * Super_Admin always passes (App\User::hasRole). Unlisted routes are left
 * to their controllers / open to any logged-in user.
 */
class EnforceRouteAccess
{
    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();
        if (! $route) {
            return $next($request);
        }

        $roles = $this->requiredRoles($request->method(), $route->uri());
        if ($roles === null) {
            return $next($request);
        }

        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        abort_unless($user->hasAnyRole(explode('|', $roles)), 403, 'Keine Berechtigung für diese Seite.');

        return $next($request);
    }

    /** First matching entry wins; null = not restricted here. */
    protected function requiredRoles(string $method, string $uri): ?string
    {
        foreach (config('route_access', []) as $key => $roles) {
            $pattern = $key;
            if (preg_match('/^(GET|POST|PUT|PATCH|DELETE) (.+)$/', $key, $m)) {
                if ($m[1] !== $method && ! ($m[1] === 'GET' && $method === 'HEAD')) {
                    continue;
                }
                $pattern = $m[2];
            }

            if ($pattern === $uri || (str_contains($pattern, '*') && Str::is($pattern, $uri))) {
                return $roles; // may be null = explicitly unrestricted (checked elsewhere)
            }
        }

        return null;
    }
}
