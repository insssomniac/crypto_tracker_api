<?php

namespace Tests\Feature;

use App\Models\Subscription;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SubscriptionsTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;

    #[Test]
    public function it_creates_subscription_alert_type_price_above(): void
    {
        // Arrange
        $payload = [
            'email' => 'test@test.com',
            'symbol' => 'tBTCUSD',
            'price_limit' => '95779',
            'alert_type' => Subscription::ALERT_TYPE_PRICE_ABOVE
        ];

        // Act
        $response = $this->post(route('subscriptions.store', $payload));

        // Assert
        $response->assertStatus(200);
        $response->assertJson($payload);
        $this->assertDatabaseHas('subscriptions', $payload);
    }

    #[Test]
    public function it_creates_subscription_alert_type_percent_change(): void
    {
        // Arrange
        $payload = [
            'email' => 'test@test.com',
            'symbol' => 'tBTCUSD',
            'percent_change' => 5,
            'time_interval' => 6,
            'alert_type' => Subscription::ALERT_TYPE_PERCENT_CHANGE
        ];

        // Act
        $response = $this->post(route('subscriptions.store', $payload));

        // Assert
        $response->assertStatus(200);
        $response->assertJson($payload);
        $this->assertDatabaseHas('subscriptions', $payload);
    }

    #[Test]
    public function it_returns_error_when_incorrect_fields_for_type_price_above(): void
    {
        // Arrange
        $payload = [
            'email' => 'test@test.com',
            'symbol' => 'tBTCUSD',
            'percent_change' => 5,
            'time_interval' => 6,
            'alert_type' => Subscription::ALERT_TYPE_PRICE_ABOVE
        ];

        // Act
        $response = $this->post(route('subscriptions.store', $payload));

        // Assert
        $response->assertSessionHasErrors(['price_limit' => 'The price limit field is required when alert type is priceAbove.']);
        $this->assertDatabaseMissing('subscriptions', $payload);
    }

    #[Test]
    public function it_returns_error_when_incorrect_fields_for_type_percent_change(): void
    {
        // Arrange
        $payload = [
            'email' => 'test@test.com',
            'symbol' => 'tBTCUSD',
            'price_limit' => '95779',
            'alert_type' => Subscription::ALERT_TYPE_PERCENT_CHANGE
        ];

        // Act
        $response = $this->post(route('subscriptions.store', $payload));

        // Assert
        $response->assertSessionHasErrors([
            'percent_change' => 'The percent change field is required when alert type is percentChange.',
            'time_interval' => 'The time interval field is required when alert type is percentChange.'
        ]);
        $this->assertDatabaseMissing('subscriptions', $payload);
    }

    #[Test]
    public function it_deletes_subscriptions_by_email()
    {
        // Arrange
        Subscription::factory()->times(3)->create(
            [
                'email' => 'test_customer@test.com'
            ]
        );

        // Act
        $response = $this->delete(route('subscriptions.bulk.destroy', ['email' => 'test_customer@test.com']));

        // Assert
        $response->assertStatus(204);
        $this->assertDatabaseMissing('subscriptions', ['email' => 'test_customer@test.com']);
    }

    #[Test]
    public function it_deletes_subscription_by_id()
    {
        // Arrange
        $subscription = Subscription::factory()->create();

        // Act
        $response = $this->delete(route('subscriptions.destroy', $subscription->id));

        // Assert
        $response->assertStatus(204);
        $this->assertDatabaseMissing('subscriptions', ['id' => $subscription->id]);
    }
}
