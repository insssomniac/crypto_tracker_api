<?php

namespace Database\Factories;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    /**
     * Define the model's default state with alert type 'priceAbove'.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->email(),
            'symbol' => 'tBTCUSD',
            'price_limit' => fake()->numberBetween(90000, 110000),
            'percent_change' => null,
            'time_interval' => null,
            'alert_type' => Subscription::ALERT_TYPE_PRICE_ABOVE,
        ];
    }

    public function typePercentChange(): self
    {
        return $this->state(fn (array $attributes) => [
            'price_limit' => null,
            'percent_change' => 1,
            'time_interval' => 6,
            'alert_type' => Subscription::ALERT_TYPE_PERCENT_CHANGE,
        ]);
    }
}
