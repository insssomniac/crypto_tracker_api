<?php

namespace App\Http\Controllers\v1;

use App\DataProviders\Bitfinex;
use App\Http\Controllers\Controller;
use App\Http\Requests\PricesRequest;
use Illuminate\Support\Carbon;

class PricesController extends Controller
{
    public function index(PricesRequest $request, Bitfinex $provider) {
        if ($request->end) {
            $carbonDate = Carbon::parse($request->end);
            $timestampMs = $carbonDate->timestamp * 1000;
        }

        return $provider->getPrices($request->symbol, $request->limit, $timestampMs ?? null);
    }
}
