<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NormalizeWindowsAuthEncoding
{
    public function handle(Request $request, Closure $next)
    {
        $authUser = $request->server('AUTH_USER');

        if ($authUser && ! mb_check_encoding($authUser, 'UTF-8')) {
            $authUser = mb_convert_encoding(
                $authUser,
                'UTF-8',
                'Windows-1252'
            );

            $request->server->set('AUTH_USER', $authUser);
        }

        return $next($request);
    }
}