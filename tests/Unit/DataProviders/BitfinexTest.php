<?php

namespace Tests\Unit\DataProviders;

use App\DataProviders\Bitfinex;
use App\Exceptions\RateLimitException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BitfinexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::createFromTimestampMs(1739005200000));
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $this->client = new Client(['handler' => $handlerStack]);
        $this->bitfinex = new Bitfinex($this->client);
    }

    #[Test]
    public function it_returns_cached_prices()
    {
        $symbol = 'BTCUSD';
        $period = 24;
        $cachedData = [
            [
                'symbol' => 'BTCUSD',
                'open' => 95976,
                'close' => 96160,
                'high' => 96160,
                'low' => 95976,
                'time' => '2025-02-08T09:00:00+00:00'
            ],
        ];

        Cache::put(Carbon::now()->format('mdH_') . $symbol . '_' . $period . '_0', $cachedData);

        $prices = $this->bitfinex->getPrices($symbol, $period);

        $this->assertEquals($cachedData, $prices);
    }

    #[Test]
    public function it_fetches_prices_when_not_cached()
    {
        $symbol = 'BTCUSD';
        $period = 24;
        $responseBody = [
            [
                1739005200000,
                95976,
                96160,
                96160,
                95976,
                2.25736752
            ],
        ];

        $this->mockHandler->append(new Response(200, [], json_encode($responseBody)));

        $prices = $this->bitfinex->getPrices($symbol, $period);

        $expectedPrices = [
            [
                'symbol' => 'BTCUSD',
                'open' => 95976,
                'close' => 96160,
                'high' => 96160,
                'low' => 95976,
                'time' => Carbon::createFromTimestampMs(1739005200000)->toIso8601String(),
            ],
        ];

        $this->assertEquals($expectedPrices, $prices);
        $this->assertTrue(Cache::has(Carbon::now()->format('mdH_') . $symbol . '_' . $period . '_0'));
    }

    #[Test]
    public function it_fetches_historical_prices()
    {
        $symbol = 'BTCUSD';
        $period = 24;
        $periodsShift = 1;
        $responseBody = [
            [
                1739005200000,
                95976,
                96160,
                96160,
                95976,
                2.25736752
            ],
        ];

        $this->mockHandler->append(new Response(200, [], json_encode($responseBody)));

        $prices = $this->bitfinex->getPrices($symbol, $period, $periodsShift);

        $expectedPrices = [
            [
                'symbol' => 'BTCUSD',
                'open' => 95976,
                'close' => 96160,
                'high' => 96160,
                'low' => 95976,
                'time' => Carbon::createFromTimestampMs(1739005200000)->toIso8601String(),
            ],
        ];

        $this->assertEquals($expectedPrices, $prices);
        $this->assertTrue(Cache::has(Carbon::now()->format('mdH_') . $symbol . '_' . $period . '_' . $periodsShift));
    }

    #[Test]
    public function it_handles_rate_limiting()
    {
        $symbol = 'BTCUSD';
        $period = 24;

        RateLimiter::shouldReceive('attempt')
            ->with('bitfinex_candles_request', 15, \Mockery::on(function ($callback) {
                return $callback() === true;
            }), 30)
            ->andReturn(false);

        $this->expectException(RateLimitException::class);
        $this->expectExceptionMessage('Rate limit exceeded');

        $this->bitfinex->getPrices($symbol, $period);
    }

    #[Test]
    public function it_handles_guzzle_exceptions()
    {
        $symbol = 'BTCUSD';
        $period = 24;

        $exception = new RequestException('Error Communicating with Server', new Request('GET', 'https://api-pub.bitfinex.com/v2/candles/trade:1h:BTCUSD/hist'));
        $this->mockHandler->append($exception);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error Communicating with Server');

        $this->bitfinex->getPrices($symbol, $period);
    }

    #[Test]
    public function it_gets_extremes_for_period()
    {
        $symbol = 'BTCUSD';
        $period = 24;
        $responseBody = [
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

        $this->mockHandler->append(new Response(200, [], json_encode($responseBody)));

        $extremes = $this->bitfinex->getExtremesForPeriod($symbol, $period);

        $expectedExtremes = [
            'min' => 95971,
            'max' => 96339
        ];

        $this->assertEquals($expectedExtremes, $extremes);
    }
}
