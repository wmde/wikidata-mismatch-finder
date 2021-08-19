<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
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
        $locale = $request->query('uselang');
        $translated = $locale && file_exists(public_path('i18n/' .$locale . '.json'));

        if($translated){
            App::setLocale($locale);
        }

        return $next($request);
    }
}
