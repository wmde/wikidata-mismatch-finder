<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * This middleware will force a RuntimeException for testing purposes, if the
 * 'X-Mismatch-Results-Error' HTTP header field is sent with the request. It
 * allows for simple feature tests of the error routing behaviour as well as
 * easy verification by testers, using a browser extension such as
 * https://addons.mozilla.org/de/firefox/addon/modheader-firefox/
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
        if ($request->header('X-Mismatch-Results-Error')) {
            throw new RuntimeException("Simulated Server Error");
        }
    
        return $next($request);
    }
}
