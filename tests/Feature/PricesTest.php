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
                "tBTCUSD",
                95775,
                null,
                95776,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                1738962003000
            ]
        ];

        $this->mockGuzzle($payload);

        // Act
        $response = $this->get('/api/v1/prices?symbol=tBTCUSD&limit=1');

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            [
                'symbol' => 'tBTCUSD',
                'bid' => 95775,
                'ask' => 95776,
                'time' => '2025-02-07T21:00:03+00:00',
            ]
        ]);
    }
}
