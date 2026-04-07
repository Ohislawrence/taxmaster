<?php

namespace App\Notifications;

use App\Models\BusinessSubscription;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SubscriptionExpiringNotification extends Notification
{
    public function __construct(
        public BusinessSubscription $subscription,
        public int $daysRemaining
    ) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $planName = $this->subscription->plan->name ?? ucfirst($this->subscription->plan_type);
        $business = $this->subscription->business;

        $message = (new MailMessage)
            ->subject("⏰ Your {$planName} subscription expires in {$this->daysRemaining} " . ($this->daysRemaining === 1 ? 'day' : 'days'))
            ->greeting("Hello {$notifiable->name},")
            ->line("Your **{$planName}** subscription for **{$business->name}** will expire soon.");

        $message->line("**Expiration Date:** " . $this->subscription->renews_at->format('F j, Y'))
            ->line("**Days Remaining:** {$this->daysRemaining} " . ($this->daysRemaining === 1 ? 'day' : 'days'));

        if ($this->daysRemaining <= 1) {
            $message->line('⚠️ **Important:** After expiration, your account will be automatically downgraded to the Free plan.')
                ->line('You will lose access to premium features including:')
                ->line('• Advanced tax filing (CIT, VAT, CGT)')
                ->line('• AI-powered analysis and optimization')
                ->line('• Bank account integration')
                ->line('• Premium support');
        }

        $amount = $this->subscription->billing_cycle === 'annual'
            ? $this->subscription->plan->annual_price
            : $this->subscription->plan->monthly_price;

        $message->line("**Renewal Amount:** ₦" . number_format($amount, 2))
            ->action('Renew Subscription', route('business.subscription'))
            ->line('To continue enjoying premium features, please renew your subscription before it expires.')
            ->line('Thank you for using TaxMaster!');

        return $message;
    }

    public function toDatabase($notifiable)
    {
        return [
            'subscription_id' => $this->subscription->id,
            'business_id' => $this->subscription->business_id,
            'plan_name' => $this->subscription->plan->name ?? $this->subscription->plan_type,
            'days_remaining' => $this->daysRemaining,
            'expires_at' => $this->subscription->renews_at->toIso8601String(),
            'type' => 'subscription_expiring',
        ];
    }
}
