<?php

namespace App\Http\Controllers\v1;

use App\DataProviders\Bitfinex;
use App\Http\Controllers\Controller;
use App\Http\Requests\PricesRequest;
use Illuminate\Http\JsonResponse;
use \Exception;

class PricesController extends Controller
{
    public function index(PricesRequest $request, Bitfinex $provider): JsonResponse
    {
        try {
            $prices = $provider->getPrices($request->symbol, $request->period, $request->shift);

            return response()->json($prices);
        } catch (Exception $e) {

            return response()->json(['errors' => 'Failed to retrieve prices. Please try again later.'], 500);
        }
    }
}
