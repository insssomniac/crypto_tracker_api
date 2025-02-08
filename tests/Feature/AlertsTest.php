<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Notifications\PercentChangeAlert;
use App\Notifications\PriceAboveAlert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AlertsTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;

    #[Test]
    public function it_handles_price_above_subscriptions()
    {
        // Arrange
        Notification::fake();

        $subscription = Subscription::factory()->create([
            'price_limit' => '100000',
        ]);

        $dataFromProvider = [
            [
                1739005200000,
                95976,
                96160,
                100001,
                95976,
                2.25736752
            ],
        ];

        $this->mockGuzzle($dataFromProvider);

        // Act
        $this->artisan('app:handle-price-above-alerts')
            ->assertExitCode(0);

        // Assert
        Notification::assertSentTo(
            new AnonymousNotifiable(),
            PriceAboveAlert::class,
            function ($notification) use ($subscription) {
                return $notification->subscription->is($subscription);
            }
        );

        $this->assertDatabaseMissing('subscriptions', ['id' => $subscription->id]);
    }

    #[Test]
    public function it_handles_percent_change_subscriptions()
    {
        // Arrange
        Notification::fake();

        $subscription = Subscription::factory()->typePercentChange()->create();

        $dataFromProvider = [
            [
                1739005200000,
                95976,
                96160,
                96160,
                90000,
                2.25736752
            ],
            [
                1739005200000,
                95976,
                96160,
                100001,
                95976,
                2.25736752
            ],
        ];

        $this->mockGuzzle($dataFromProvider);

        // Act
        $this->artisan('app:handle-percent-change-alerts')
            ->assertExitCode(0);

        // Assert
        Notification::assertSentTo(
            new AnonymousNotifiable(),
            PercentChangeAlert::class,
            function ($notification) use ($subscription) {
                return $notification->subscription->is($subscription);
            }
        );

        $this->assertDatabaseMissing('subscriptions', ['id' => $subscription->id]);
    }
}
