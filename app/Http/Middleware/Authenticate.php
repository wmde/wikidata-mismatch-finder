<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

/**
 * Middleware to authenticate
 *
 * Provided by Laravel scaffold
 */
class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string|null
     */
    protected function redirectTo($request) // phpcs:ignore
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }
}
