<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendBroadcastEmail;
use App\Mail\BroadcastMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class BroadcastEmailController extends Controller
{
    public function create()
    {
        return Inertia::render('Admin/Broadcast/Create');
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'roles' => 'nullable|array',
            'roles.*' => 'string',
            'subscribed' => 'nullable|in:all,subscribed,unsubscribed',
        ]);

        $query = User::query();

        // filter by roles if provided
        if (!empty($data['roles'])) {
            $query->whereHas('roles', function ($q) use ($data) {
                $q->whereIn('name', $data['roles']);
            });
        }

        // subscription filter: expects `subscribed` status on users or on subscriptions table
        if (!empty($data['subscribed']) && $data['subscribed'] !== 'all') {
            if ($data['subscribed'] === 'subscribed') {
                $query->whereHas('subscriptions');
            } else {
                $query->whereDoesntHave('subscriptions');
            }
        }

        // dispatch jobs in chunks to avoid memory spikes
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
