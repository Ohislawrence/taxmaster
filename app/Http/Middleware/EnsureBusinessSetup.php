<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBusinessSetup
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If user is authenticated and verified but has not owned a business, redirect to setup
        if (
            auth()->check() &&
            auth()->user()->hasVerifiedEmail() &&
            !(
                auth()->user()->ownedBusiness()->exists() ||
                auth()->user()->businesses()->exists()
            )
        ) {
            return redirect()->route('business.setup.create');
        }

        return $next($request);
    }
}
