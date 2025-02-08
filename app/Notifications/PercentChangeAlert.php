<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PercentChangeAlert extends Notification
{
    use Queueable;

    public Subscription $subscription;
    private int|float $percentChange;
    private int $interval;
    private int|float $highestPrice;
    private int|float $lowestPrice;

    /**
     * Create a new notification instance.
     */
    public function __construct(Subscription $subscription, int|float $percentChange, int $interval, int|float $highestPrice, int|float $lowestPrice)
    {
        $this->subscription = $subscription;
        $this->percentChange = $percentChange;
        $this->interval = $interval;
        $this->highestPrice = $highestPrice;
        $this->lowestPrice = $lowestPrice;
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
            ->subject('Price Change Exceeded Percentage Notification')
            ->line('The percent of price change of ' . $this->subscription->symbol . ' has exceeded your specified limit.')
            ->line('Highest price for '. $this->subscription->time_interval .' hours interval: ' . $this->highestPrice)
            ->line('Lowest price for '. $this->subscription->time_interval .' hours interval: ' . $this->lowestPrice)
            ->line('Percent change: ' . $this->percentChange)
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
            'percent_change' => $this->subscription->percent_change,
            'percent_change_actual' => $this->percentChange,
            'interval' => $this->interval,
            'highest_price' => $this->highestPrice,
            'lowest_price' => $this->lowestPrice,
        ];
    }
}
