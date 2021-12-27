<?php

namespace App\Http\Middleware;

use Closure;
use App;

class Locale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($request->has('locale')){
            App::setLocale($request->get('locale'));
        }else{
            App::setLocale('ka');
        }
        return $next($request);
    }
}
