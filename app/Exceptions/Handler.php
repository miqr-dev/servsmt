<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Inertia\Inertia;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        $response = parent::render($request, $exception);
        $status = $response->getStatusCode();

        // Modern error page (resources/js/pages/Error.vue) instead of the old
        // AdminLTE errors/*.blade.php - for browser and Inertia requests.
        // JSON/AJAX callers keep Laravel's normal response; 500/503 only when
        // not debugging, so the Laravel error page with the stack trace stays
        // available locally.
        $pages = [403, 404, 419, 429];
        if (! config('app.debug')) {
            $pages = array_merge($pages, [500, 503]);
        }

        $wantsPage = $request->header('X-Inertia') || ! $request->expectsJson();
        if ($wantsPage && in_array($status, $pages, true)) {
            if ($status === 419) {
                // Expired CSRF token: send the user back with a hint instead.
                return back()->with('error', 'Die Sitzung ist abgelaufen. Bitte noch einmal versuchen.');
            }

            return Inertia::render('Error', ['status' => $status])
                ->toResponse($request)
                ->setStatusCode($status);
        }

        return $response;
    }
}
