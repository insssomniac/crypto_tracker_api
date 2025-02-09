<?php

use App\Http\Controllers\v1\PricesController;
use App\Http\Controllers\v1\SubscriptionsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/prices', [PricesController::class, 'index'])->name('prices.get');
    Route::delete('subscriptions/bulk', [SubscriptionsController::class, 'bulkDestroy'])->name('subscriptions.bulk.destroy');
    Route::apiResource('subscriptions', SubscriptionsController::class)->only('store', 'destroy')->names('subscriptions');
});
