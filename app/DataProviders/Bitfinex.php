<?php

namespace App\DataProviders;

use Illuminate\Support\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Bitfinex
{
    private string $apiVersion = 'v2';
    private string $apiUrl = 'https://api-pub.bitfinex.com/';

    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get prices for $limit hours
     *
     * @throws GuzzleException
     */
    public function getPrices(string $symbol, int $limit, ?string $end = null): array
    {
        $response = $this->client->request('GET', $this->apiUrl . $this->apiVersion . '/tickers/hist', [
            'headers' => [
                'accept' => 'application/json',
            ],
            'query' => [
                'symbols' => $symbol,
                'limit' => $limit,
                'end' => $end,
            ]
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return $this->parseHistory($data);
    }

    private function parseHistory(array $responseContents): array
    {
        $result = [];

        foreach ($responseContents as $item) {
            $timestamp = $item[12];
            $datetime = Carbon::createFromTimestampMs($timestamp)->toIso8601String();

            $result[] = [
                'symbol' => $item[0],
                'bid' => $item[1],
                'ask' => $item[3],
                'time' => $datetime,
            ];
        }

        return $result;
    }
}
