<?php

namespace App\DataProviders;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class Bitfinex
{
    private string $apiVersion = 'v2';
    private string $apiUrl = 'https://api-pub.bitfinex.com/';

    private Client $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get prices for $period hours. $period = 24 will return day, 168 - week.
     *
     * @param string $symbol
     * @param int $period
     * @param int|null $periodsShift
     * @return array
     */
    public function getPrices(string $symbol, int $period, ?int $periodsShift = 0): array
    {
        $cachedData = Cache::get(Carbon::now()->format('mdH_') . $symbol . '_' . $period . '_' . $periodsShift);

        if ($cachedData) {
            return $cachedData;
        }

        $prices = $this->requestPrices($symbol, $period, $periodsShift);

        // If shift is indicated (requesting historical data), then cache data for an hour
        // If shift is null, then cache data for 1 minute (every minute updates)
        $cacheTime = $periodsShift ? 60 : 1;

        Cache::put(Carbon::now()->format('YmdH_') . $symbol . '_' . $period . '_' . $periodsShift, $prices, $cacheTime);

        return $prices;
    }

    /**
     * @param string $symbol
     * @param int $period
     * @return array
     */
    public function getExtremesForPeriod(string $symbol, int $period): array
    {
        $pricesCollection = collect($this->getPrices($symbol, $period));

        return [
            'min' => $pricesCollection->min('low'),
            'max' => $pricesCollection->max('high')
        ];
    }

    /**
     * @param string $symbol
     * @param int $period
     * @param int|null $periodsShift
     * @return array
     * @throws Exception | GuzzleException
     */
    protected function requestPrices(string $symbol, int $period, ?int $periodsShift = 0)
    {
        $query = ['limit' => $period];

        if ($periodsShift) {
            $query['end'] = Carbon::now()->subHours($period * $periodsShift)->getTimestampMs();
        }

        try {
            $response = $this->client->request('GET', $this->apiUrl . $this->apiVersion . '/candles/trade:1h:' . $symbol . '/hist', [
                'headers' => [
                    'accept' => 'application/json',
                ],
                'query' => $query,
            ]);
        } catch (Exception $e) {
            logger()->error('[ERROR: FAILED_TO_RETRIEVE_PRICES] ' . $e->getMessage(), $e->getTrace());

            throw $e;
        }

        $responseData = json_decode($response->getBody()->getContents(), true);

        return $this->parseHistory($responseData, $symbol);
    }

    private function parseHistory(array $responseContents, string $symbol): array
    {
        $result = [];

        foreach ($responseContents as $item) {
            $result[] = [
                'symbol' => $symbol,
                'open' => $item[1],
                'close' => $item[2],
                'high' => $item[3],
                'low' => $item[4],
                'time' => Carbon::createFromTimestampMs($item[0])->toIso8601String(),
            ];
        }

        return $result;
    }
}
