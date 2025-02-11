<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PricesTest extends TestCase
{
    #[Test]
    public function it_gets_prices(): void
    {
        // Arrange
        $payload = [
            [
                1739005200000,
                95976,
                96160,
                96160,
                95976,
                2.25736752
            ],
            [
                1739001600000,
                96282,
                95976,
                96339,
                95971,
                2.76385881
            ],
        ];

        $this->mockGuzzle($payload);

        // Act
        $response = $this->get(route('prices.get', [
            'symbol' => 'tBTCUSD',
            'period' => 2
        ]));

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            [
                "symbol" => "tBTCUSD",
                "open" => 95976,
                "close" => 96160,
                "high" => 96160,
                "low" => 95976,
                "time" => "2025-02-08T09:00:00+00:00"
            ],
            [
                "symbol" => "tBTCUSD",
                "open" => 96282,
                "close" => 95976,
                "high" => 96339,
                "low" => 95971,
                "time" => "2025-02-08T08:00:00+00:00"
            ]
        ]);
    }

    #[Test]
    public function it_returns_422_for_missing_parameters(): void
    {
        // Act
        $response = $this->get(route('prices.get'), ['accept' => 'Application/json']);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['symbol', 'period']);
    }

    #[Test]
    public function it_handles_exception_from_provider(): void
    {
        // Arrange
        $this->mockGuzzle([], 500); // Simulate an error from the data provider

        // Act
        $response = $this->get(route('prices.get', [
            'symbol' => 'tBTCUSD',
            'period' => 2
        ]));

        // Assert
        $response->assertStatus(500);
        $response->assertJson(['errors' => 'Failed to retrieve prices. Please try again later.']);
    }
}
