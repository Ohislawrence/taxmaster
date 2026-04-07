<?php

namespace App\Notifications;

use App\Models\BusinessSubscription;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SubscriptionDowngradedNotification extends Notification
{
    public function __construct(
        public BusinessSubscription $oldSubscription,
        public BusinessSubscription $newSubscription
    ) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $oldPlanName = $this->oldSubscription->plan->name ?? ucfirst($this->oldSubscription->plan_type);
        $business = $this->oldSubscription->business;

        $message = (new MailMessage)
            ->subject("Your subscription has been downgraded to Free plan")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your **{$oldPlanName}** subscription for **{$business->name}** has expired and been automatically downgraded to the Free plan.");

        $message->line('**What this means:**')
            ->line('✓ You can still use basic tax filing features (PAYE, WHT)')
            ->line('✓ Your existing data remains safe and accessible')
            ->line('✗ Premium features are no longer available:')
            ->line('  • Advanced tax filing (CIT, VAT, CGT)')
            ->line('  • AI-powered analysis and optimization')
            ->line('  • Bank account integration')
            ->line('  • Priority support');

        $message->action('Upgrade to Continue Premium Features', route('business.subscription'))
            ->line('You can upgrade at any time to regain access to all premium features.')
            ->line('Thank you for using TaxMaster!');

        return $message;
    }

    public function toDatabase($notifiable)
    {
        return [
            'old_subscription_id' => $this->oldSubscription->id,
            'new_subscription_id' => $this->newSubscription->id,
            'business_id' => $this->oldSubscription->business_id,
            'old_plan_name' => $this->oldSubscription->plan->name ?? $this->oldSubscription->plan_type,
            'new_plan_name' => 'Free',
            'downgraded_at' => now()->toIso8601String(),
            'type' => 'subscription_downgraded',
        ];
    }
}
