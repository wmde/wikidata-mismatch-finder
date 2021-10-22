<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * This middleware will force a RuntimeException for testing purposes, if the
 * 'X-Mismatch-Finder-Error' HTTP header field is sent with the request and
 * its value matches the request path. It allows for simple feature tests of
 * the error routing behaviour as well as easy verification by testers, using
 * a browser extension such as https://addons.mozilla.org/de/firefox/addon/modheader-firefox/
 */
class SimulateError
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->header('X-Mismatch-Finder-Error') == $request->path()) {
            throw new RuntimeException("Simulated Server Error");
        } elseif ($request->header('X-Mismatch-Finder-Not-Found') == $request->path()) {
            abort(404);
        }

        return $next($request);
    }
}
