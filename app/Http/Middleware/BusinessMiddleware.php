<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BusinessMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->hasRole('business')) {
            return $next($request);
        }

        return redirect('/')->with('error', 'Unauthorized access.');
    }
}
