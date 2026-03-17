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
        // Only enforce business setup for users with the 'business' role.
        // Accountants, admins and other roles should not be forced to complete business setup.
        if (auth()->check() && auth()->user()->hasVerifiedEmail() && method_exists(auth()->user(), 'isBusiness') && auth()->user()->isBusiness()) {
            if (!(auth()->user()->ownedBusiness()->exists() || auth()->user()->businesses()->exists())) {
                return redirect()->route('business.setup.create');
            }
        }

        return $next($request);
    }
}
