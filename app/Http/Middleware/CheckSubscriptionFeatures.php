<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\SubscriptionService;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionFeatures
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $feature
     */
    public function handle(Request $request, Closure $next, string $feature = ''): Response
    {
        $business = auth()->user()?->ownedBusiness;

        if (!$business) {
            return response()->json(['error' => 'No business selected'], 403);
        }

        // Check if business can perform action
        if (!$this->subscriptionService->canPerformAction($business, $feature)) {
            // For GET requests (page views), redirect back with modal data
            if ($request->isMethod('get')) {
                return redirect()->back()->with([
                    'upgrade_modal' => [
                        'show' => true,
                        'feature' => $feature,
                        'message' => 'This feature is not available on your current plan. Please upgrade to access it.',
                        'upgrade_url' => route('business.plans.index'),
                    ],
                ]);
            }

            // For API requests, return JSON error
            return response()->json([
                'error' => 'This feature is not available on your current plan',
                'action' => 'upgrade',
                'feature' => $feature,
            ], 403);
        }

        return $next($request);
    }
}
