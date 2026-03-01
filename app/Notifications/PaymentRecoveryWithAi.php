<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentRecoveryWithAi extends Notification
{
    public function __construct(
        public $subscription,
        public $aiSuggestion
    ) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $strategy = $this->aiSuggestion['recovery_strategy'] ?? 'gentle_reminder';
        $business = $this->subscription->business;

        $message = (new MailMessage)
            ->subject('💳 Payment Issue - We\'re Here to Help')
            ->greeting("Hello {$notifiable->name},");

        $message->line("We noticed that your recent payment for **{$business->name}** didn't go through.")
            ->line("The good news? We have a solution tailored to your situation.");

        // Add strategy-specific message
        switch ($strategy) {
            case 'offer_discount':
                $discount = $this->aiSuggestion['suggested_discount'] ?? 10;
                $message->line("**We're offering you a {$discount}% discount** on your next {$subscription->billing_cycle} to help you get back on track.");
                break;

            case 'payment_plan':
                $plan = $this->aiSuggestion['payment_plan'] ?? [];
                $months = $plan['months'] ?? 0;
                $message->line("**We can set up a flexible payment plan** - split across {$months} months with manageable payments.");
                break;

            case 'pause_service':
                $message->line("**We can pause your service temporarily** while you sort out your payment method, with no data loss.");
                break;

            default:
                $message->line("**Let's get this sorted together.**");
        }

        $message->line($this->aiSuggestion['messaging'] ?? '')
            ->action('Resolve Payment Issue', route('business.subscription'))
            ->line('**In the meantime, here\'s what we recommend:**')
            ->line('• Check your payment method is current')
            ->line('• Ensure sufficient funds are available')
            ->line('• Contact us if you need to discuss payment options')
            ->line('We\'re here to help make it easy for you!')
            ->line('Support: support@taxmaster.ng');

        return $message;
    }

    public function toDatabase($notifiable)
    {
        return [
            'subscription_id' => $this->subscription->id,
            'business_id' => $this->subscription->business_id,
            'strategy' => $this->aiSuggestion['recovery_strategy'] ?? 'gentle_reminder',
            'suggested_discount' => $this->aiSuggestion['suggested_discount'] ?? 0,
            'recovery_probability' => $this->aiSuggestion['probability_of_recovery'] ?? 0,
            'risk_assessment' => $this->aiSuggestion['risk_assessment'] ?? '',
            'action_required' => true,
        ];
    }
}
