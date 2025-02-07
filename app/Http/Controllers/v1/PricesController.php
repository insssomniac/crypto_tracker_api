<?php

namespace App\Http\Controllers\v1;

use App\DataProviders\Bitfinex;
use App\Http\Controllers\Controller;
use App\Http\Requests\PricesRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use \Exception;

class PricesController extends Controller
{
    public function index(PricesRequest $request, Bitfinex $provider): JsonResponse
    {
        try {
            if ($request->end) {
                $carbonDate = Carbon::parse($request->end);
                $timestampMs = $carbonDate->timestamp * 1000;
            }

            $prices = $provider->getPrices($request->symbol, $request->limit, $timestampMs ?? null);

            return response()->json($prices);
        } catch (Exception $e) {

            return response()->json(['errors' => 'Failed to retrieve prices. Please try again later.'], 500);
        }
    }
}
