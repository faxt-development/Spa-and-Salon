<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class StartSessionMiddleware
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
        // Ensure session is started
        if (!session()->isStarted()) {
            session()->start();
        }
        
        return $next($request);
    }
}
