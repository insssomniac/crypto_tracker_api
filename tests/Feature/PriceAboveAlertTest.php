<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Notifications\PriceAboveAlert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PriceAboveAlertTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;

    #[Test]
    public function it_can_create_a_price_above_alert_notification(): void
    {
        $subscription = Subscription::factory()->create([
            'symbol' => 'BTCUSD',
            'price_limit' => 100000,
        ]);

        $priceLevel = 100001;

        $notification = new PriceAboveAlert($subscription, $priceLevel);

        $this->assertInstanceOf(PriceAboveAlert::class, $notification);
        $this->assertEquals($subscription, $notification->subscription);
        $this->assertEquals($priceLevel, $notification->priceLevel);
    }

    #[Test]
    public function it_can_send_mail_notification()
    {
        NotificationFacade::fake();

        $subscription = Subscription::factory()->create([
            'symbol' => 'BTCUSD',
            'price_limit' => 100000,
            'email' => 'test@example.com',
        ]);

        $priceLevel = 100001;

        $notification = new PriceAboveAlert($subscription, $priceLevel);

        // Simulate sending the notification
        NotificationFacade::send([$subscription], $notification);

        // Assert that the notification was sent
        NotificationFacade::assertSentTo(
            [$subscription],
            PriceAboveAlert::class,
            function ($notification, $channels, $notifiable) use ($subscription, $priceLevel) {
                return $notifiable->routeNotificationFor('mail', null) === $notifiable->email &&
                    $notification->subscription->is($subscription) &&
                    $notification->priceLevel === $priceLevel;
            }
        );
    }

    #[Test]
    public function it_can_convert_to_array()
    {
        $subscription = Subscription::factory()->create([
            'symbol' => 'BTCUSD',
            'price_limit' => 100000,
        ]);

        $priceLevel = 100001;

        $notification = new PriceAboveAlert($subscription, $priceLevel);

        $expectedArray = [
            'symbol' => 'BTCUSD',
            'current_price' => 100001,
            'price_limit' => 100000,
        ];

        $this->assertEquals($expectedArray, $notification->toArray(new AnonymousNotifiable()));
    }

    #[Test]
    public function it_can_get_delivery_channels()
    {
        $subscription = Subscription::factory()->create();

        $priceLevel = 100001;

        $notification = new PriceAboveAlert($subscription, $priceLevel);

        $this->assertEquals(['mail'], $notification->via(new AnonymousNotifiable()));
    }

    #[Test]
    public function it_has_correct_mail_subject_and_content()
    {
        $subscription = Subscription::factory()->create([
            'symbol' => 'BTCUSD',
            'price_limit' => 100000,
        ]);

        $priceLevel = 100001;

        $notification = new PriceAboveAlert($subscription, $priceLevel);

        $mailMessage = $notification->toMail(new AnonymousNotifiable());

        $this->assertEquals('Price Limit Exceeded Notification', $mailMessage->subject);
        $this->assertStringContainsString('The price of BTCUSD has exceeded your specified limit.', $mailMessage->render());
        $this->assertStringContainsString('Highest Price: 100001', $mailMessage->render());
        $this->assertStringContainsString('Price Limit: 100000', $mailMessage->render());
        $this->assertStringContainsString('Thank you for using our application!', $mailMessage->render());
    }
}
