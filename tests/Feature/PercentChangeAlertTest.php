<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Notifications\PercentChangeAlert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PercentChangeAlertTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;

    #[Test]
    public function it_can_create_a_percent_change_alert_notification()
    {
        $subscription = Subscription::factory()->create([
            'symbol' => 'BTCUSD',
            'time_interval' => 1,
            'percent_change' => 5,
        ]);

        $percentChange = 10.5;
        $interval = 1;
        $highestPrice = 15000;
        $lowestPrice = 14000;

        $notification = new PercentChangeAlert($subscription, $percentChange, $interval, $highestPrice, $lowestPrice);

        $this->assertInstanceOf(PercentChangeAlert::class, $notification);
        $this->assertEquals($subscription, $notification->subscription);
        $this->assertEquals($percentChange, $notification->percentChange);
        $this->assertEquals($interval, $notification->interval);
        $this->assertEquals($highestPrice, $notification->highestPrice);
        $this->assertEquals($lowestPrice, $notification->lowestPrice);
    }

    #[Test]
    public function it_can_send_mail_notification()
    {
        NotificationFacade::fake();

        $subscription = Subscription::factory()->create([
            'symbol' => 'BTCUSD',
            'time_interval' => 6,
        ]);

        $percentChange = 1;
        $interval = 6;
        $highestPrice = 15000;
        $lowestPrice = 14000;

        $notification = new PercentChangeAlert($subscription, $percentChange, $interval, $highestPrice, $lowestPrice);

        // Simulate sending the notification
        $subscription->notify($notification);

        // Assert that the notification was sent
        NotificationFacade::assertSentTo(
            [$subscription],
            PercentChangeAlert::class,
            function ($notification, $channels, $notifiable) {
                return $notifiable->routeNotificationFor('mail', null) === $notifiable->email &&
                    $notification->percentChange === 1 &&
                    $notification->highestPrice === 15000 &&
                    $notification->lowestPrice === 14000;
            }
        );
    }

    #[Test]
    public function it_can_convert_to_array()
    {
        $subscription = Subscription::factory()->create([
            'symbol' => 'BTCUSD',
            'percent_change' => 5,
        ]);

        $percentChange = 10.5;
        $interval = 1;
        $highestPrice = 15000;
        $lowestPrice = 14000;

        $notification = new PercentChangeAlert($subscription, $percentChange, $interval, $highestPrice, $lowestPrice);

        $expectedArray = [
            'symbol' => 'BTCUSD',
            'percent_change' => 5,
            'percent_change_actual' => 10.5,
            'interval' => 1,
            'highest_price' => 15000,
            'lowest_price' => 14000,
        ];

        $this->assertEquals($expectedArray, $notification->toArray($subscription));
    }

    #[Test]
    public function it_can_get_delivery_channels()
    {
        $subscription = Subscription::factory()->create();

        $notification = new PercentChangeAlert($subscription, 10.5, 1, 15000, 14000);

        $this->assertEquals(['mail'], $notification->via($subscription));
    }

    #[Test]
    public function it_has_correct_mail_subject_and_content()
    {
        $subscription = Subscription::factory()->create([
            'symbol' => 'BTCUSD',
            'time_interval' => 6,
        ]);

        $percentChange = 1;
        $interval = 6;
        $highestPrice = 15000;
        $lowestPrice = 14000;

        $notification = new PercentChangeAlert($subscription, $percentChange, $interval, $highestPrice, $lowestPrice);

        $mailMessage = $notification->toMail(new AnonymousNotifiable());

        $this->assertEquals('Price Change Exceeded Percentage Notification', $mailMessage->subject);
        $this->assertStringContainsString('The percent of price change of BTCUSD has exceeded your specified limit.', $mailMessage->render());
        $this->assertStringContainsString('Highest price for 6 hours interval: 15000', $mailMessage->render());
        $this->assertStringContainsString('Lowest price for 6 hours interval: 14000', $mailMessage->render());
        $this->assertStringContainsString('Percent change: 1', $mailMessage->render());
        $this->assertStringContainsString('Thank you for using our application!', $mailMessage->render());
    }
}
