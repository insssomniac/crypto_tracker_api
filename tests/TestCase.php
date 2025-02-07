<?php

namespace Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Collection;

abstract class TestCase extends BaseTestCase
{
    protected Collection $capturedRequests;

    protected function mockGuzzle($payload, $status = 200, $headers = []): Collection
    {
        $this->capturedRequests = collect();
        $mock = new MockHandler([
            new Response($status, $headers, json_encode($payload))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push(Middleware::history($this->capturedRequests));

        $this->instance(Client::class, new Client(['handler' => $handlerStack]));

        return $this->capturedRequests;
    }
}
