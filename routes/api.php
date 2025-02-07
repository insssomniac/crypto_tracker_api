<?php

use App\Http\Controllers\v1\PricesController;
use App\Http\Controllers\v1\SubscriptionsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/prices', [PricesController::class, 'index']);
    Route::apiResource('subscriptions', SubscriptionsController::class);
});
