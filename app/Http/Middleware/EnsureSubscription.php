<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscription
{
    /**
     * Routes that don't require active subscription
     */
    protected array $exemptRoutes = [
        'business.plans.index',
        'business.plans.select',
        'business.plans.checkout',
        'business.plans.payment-callback',
        'business.subscription',
        'business.subscription.upgrade-plan',
        'business.settings.index',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if this route is exempt from subscription check
        if ($this->isExemptRoute($request)) {
            return $next($request);
        }

        // Get the authenticated user's business
        $business = auth()->user()->ownedBusiness;

        if (!$business) {
            return $next($request);
        }

        // Check if business has an active subscription
        $activeSubscription = $business->subscriptions()
            ->whereIn('status', ['active', 'pending_payment', 'pending'])
            ->where('renews_at', '>', now())
            ->latest()
            ->first();

        // If no active subscription, redirect to plans
        if (!$activeSubscription) {
            return redirect()->route('business.plans.index')
                ->with('message', 'Please select a subscription plan to access this feature.');
        }

        // Store subscription in request for easy access
        $request->merge(['activeSubscription' => $activeSubscription]);

        return $next($request);
    }

    /**
     * Check if the current route is exempt from subscription check
     */
    protected function isExemptRoute(Request $request): bool
    {
        $routeName = $request->route()?->getName();

        return in_array($routeName, $this->exemptRoutes) ||
               str_contains($routeName ?? '', 'logout');
    }
}
