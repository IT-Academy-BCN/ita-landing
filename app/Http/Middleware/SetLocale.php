<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): response
    {
        if (in_array($request->header('Accept-Language'), config('app.supported_locales'))) {

            App::setLocale($request->header('Accept-Language'));

            Session::put('locale', $request->header('Accept-Language'));

        } else {

            App::setLocale(config('app.locale'));

            Session::put('locale', config('app.locale'));
        }

        return $next($request);
    }
}