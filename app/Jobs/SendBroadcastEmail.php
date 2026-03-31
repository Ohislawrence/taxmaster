<?php

namespace App\Jobs;

use App\Mail\BroadcastMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendBroadcastEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $userId;
    public string $subject;
    public string $bodyTemplate;

    public function __construct(int $userId, string $subject, string $bodyTemplate)
    {
        $this->userId = $userId;
        $this->subject = $subject;
        $this->bodyTemplate = $bodyTemplate;
    }

    public function handle()
    {
        $user = User::find($this->userId);
        if (! $user || ! $user->email) return;

        $body = $this->renderForUser($this->bodyTemplate, $user);

        Mail::to($user->email)->queue(new BroadcastMail($this->subject, $body));
    }

    protected function renderForUser(string $template, User $user): string
    {
        // Tokens: {first_name}, {last_name}, {name}, {email}, {business_name}, {role}, {plan}
        $name = $user->name ?? '';
        $first = '';
        $last = '';
        if ($name) {
            $parts = preg_split('/\s+/', $name);
            $first = $parts[0] ?? '';
            $last = count($parts) > 1 ? array_pop($parts) : '';
        }

        $business = '';
        try {
            $b = $user->defaultBusiness();
            $business = $b?->name ?? '';
        } catch (\Throwable $e) {
            $business = '';
        }

        // Get user's primary role
        $role = '';
        try {
            $userRole = $user->roles->first();
            $role = $userRole ? ucfirst($userRole->name) : 'User';
        } catch (\Throwable $e) {
            $role = 'User';
        }

        // Get user's current plan
        $plan = 'Free';
        try {
            $subscription = $user->subscriptions()
                ->where('status', 'active')
                ->whereDate('ends_at', '>=', now())
                ->with('plan')
                ->first();

            if ($subscription && $subscription->plan) {
                $plan = $subscription->plan->name;
            }
        } catch (\Throwable $e) {
            $plan = 'Free';
        }

        $map = [
            '{first_name}' => $first,
            '{last_name}' => $last,
            '{name}' => $name,
            '{email}' => $user->email,
            '{business_name}' => $business,
            '{role}' => $role,
            '{plan}' => $plan,
        ];

        return strtr($template, $map);
    }
}
