<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PriceAboveAlert extends Notification
{
    use Queueable;

    public Subscription $subscription;
    public int|float $priceLevel;

    /**
     * Create a new notification instance.
     */
    public function __construct(Subscription $subscription, int|float $priceLevel)
    {
        $this->subscription = $subscription;
        $this->priceLevel = $priceLevel;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Price Limit Exceeded Notification')
            ->line('The price of ' . $this->subscription->symbol . ' has exceeded your specified limit.')
            ->line('Highest Price: ' . $this->priceLevel)
            ->line('Price Limit: ' . $this->subscription->price_limit)
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'symbol' => $this->subscription->symbol,
            'current_price' => $this->priceLevel,
            'price_limit' => $this->subscription->price_limit,
        ];
    }
}
