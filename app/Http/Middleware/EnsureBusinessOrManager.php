<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureBusinessOrManager
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('home');
        }

        // Allow users with the business role
        if (method_exists($user, 'hasRole') && $user->hasRole('business')) {
            return $next($request);
        }

        // If the user manages the business (accountant), allow access.
        // Determine business id from route parameter or session
        $routeBusiness = $request->route('business');
        $businessId = null;

        if ($routeBusiness) {
            $businessId = is_object($routeBusiness) && isset($routeBusiness->id) ? $routeBusiness->id : $routeBusiness;
        }

        if (! $businessId) {
            $businessId = $request->session()->get('business_id');
        }

        if ($businessId && method_exists($user, 'managesBusiness') && $user->managesBusiness($businessId)) {
            return $next($request);
        }

        abort(403);
    }
}
