<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

/**
 * Stops the same ticket from being created twice (2026-10-12).
 *
 * A double click (or Enter pressed twice, or a repeat after a slow answer)
 * sent the form twice; the browser drops the first answer but the server
 * still saved both. Now an identical POST from the same user to the same URL
 * within 15 seconds is not processed again: the user is sent to the page the
 * first one leads to (Meine Tickets) with "bereits gesendet".
 * If the first one fails (validation error, server error), the lock is
 * released at once, so sending again works.
 *
 * Route usage: ->middleware('single.submit:ticket.usertickets,handwerk')
 *   1st parameter: route to redirect to, 2nd: its ?tab= value (optional).
 */
class PreventDuplicateSubmit
{
    private const SECONDS = 15;

    public function handle(Request $request, Closure $next, ?string $redirectRoute = null, ?string $tab = null)
    {
        $user = $request->user();
        if (! $request->isMethod('post') || ! $user) {
            return $next($request);
        }

        $key = 'single-submit:'.$user->id.':'.$request->path().':'.$this->fingerprint($request);
        if (! Cache::add($key, 1, now()->addSeconds(self::SECONDS))) {
            $message = 'Das wurde bereits gesendet und nur einmal gespeichert.';
            if ($request->expectsJson() && ! $request->header('X-Inertia')) {
                return response()->json(['message' => $message, 'duplicate' => true], 409);
            }
            $to = $redirectRoute ? route($redirectRoute, $tab ? ['tab' => $tab] : []) : url()->previous();

            return redirect()->to($to)->with('success', $message);
        }

        $response = $next($request);

        // Failed - server error, validation errors, Excel import errors, or
        // sent back to the form: allow sending again right away.
        $session = $request->hasSession() ? $request->session() : null;
        $failed = $response->getStatusCode() >= 400
            || ($session && ($session->has('errors') || $session->has('import_errors')))
            || ($response->isRedirect() && rtrim($response->headers->get('Location', ''), '/') === rtrim(url()->previous(), '/'));
        if ($failed) {
            Cache::forget($key);
        }

        return $response;
    }

    /** Hash of the submitted fields + uploaded file names/sizes (no CSRF token). */
    private function fingerprint(Request $request): string
    {
        $files = [];
        foreach (Arr::flatten($request->allFiles()) as $file) {
            $files[] = $file->getClientOriginalName().':'.$file->getSize();
        }

        return sha1(json_encode([Arr::except($request->input(), ['_token']), $files]));
    }
}
