<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendBroadcastEmail;
use App\Mail\BroadcastMail;
use App\Models\User;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class BroadcastEmailController extends Controller
{
    public function create()
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('display_order')
            ->get(['id', 'name', 'slug']);

        return Inertia::render('Admin/Broadcast/Create', [
            'availableRoles' => ['admin', 'business', 'accountant'],
            'availablePlans' => $plans,
        ]);
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'roles' => 'nullable|array',
            'roles.*' => 'string',
            'subscribed' => 'nullable|in:all,subscribed,unsubscribed',
            'has_business' => 'nullable|in:all,yes,no',
            'plans' => 'nullable|array',
            'plans.*' => 'integer|exists:subscription_plans,id',
        ]);

        $query = User::query();

        // Filter by roles if provided
        if (!empty($data['roles'])) {
            $query->whereHas('roles', function ($q) use ($data) {
                $q->whereIn('name', $data['roles']);
            });
        }

        // Subscription filter: has any subscription or not
        if (!empty($data['subscribed']) && $data['subscribed'] !== 'all') {
            if ($data['subscribed'] === 'subscribed') {
                $query->whereHas('subscriptions');
            } else {
                $query->whereDoesntHave('subscriptions');
            }
        }

        // Filter by business creation status
        if (!empty($data['has_business']) && $data['has_business'] !== 'all') {
            if ($data['has_business'] === 'yes') {
                // Users who have created a business (as owner)
                $query->whereHas('ownedBusiness');
            } else {
                // Users who haven't created a business
                $query->whereDoesntHave('ownedBusiness');
            }
        }

        // Filter by specific subscription plans
        if (!empty($data['plans'])) {
            $query->whereHas('subscriptions.plan', function ($q) use ($data) {
                $q->whereIn('subscription_plans.id', $data['plans']);
            });
        }

        // Dispatch jobs in chunks to avoid memory spikes
        $subject = $data['subject'];
        $bodyTemplate = $data['body'];

        $query->chunk(200, function ($users) use ($subject, $bodyTemplate) {
            foreach ($users as $user) {
                SendBroadcastEmail::dispatch($user->id, $subject, $bodyTemplate);
            }
        });

        return back()->with('success', 'Broadcast queued. Emails will be sent shortly.');
    }
}
