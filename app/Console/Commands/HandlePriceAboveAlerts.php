<?php

namespace App\Console\Commands;

use App\DataProviders\Bitfinex;
use App\Models\Subscription;
use App\Notifications\PercentChangeAlert;
use App\Notifications\PriceAboveAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class HandlePriceAboveAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:handle-price-above-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks Price Above subscriptions and notifies customers';

    /**
     * Execute the console command.
     */
    public function handle(Bitfinex $dataProvider)
    {
        $symbols = Subscription::byAlertType(Subscription::ALERT_TYPE_PRICE_ABOVE)->pluck('symbol')->unique();

        foreach ($symbols as $symbol) {
            // Check the highest price of each symbol from existing subscriptions
            $highestPrice = $dataProvider->getExtremesForPeriod($symbol, 24)['max'];

            // Select only subscriptions which meets condition
            $subscriptionsToNotify = Subscription::byAlertType(Subscription::ALERT_TYPE_PRICE_ABOVE)
                ->where('price_limit', '<=', $highestPrice)
                ->get();

            logger()->debug($subscriptionsToNotify->pluck('id'));

            // Notify subscribers
            foreach ($subscriptionsToNotify as $subscription) {
                try {
                    /** @var Subscription $subscription */
                    $subscription->notify((new PriceAboveAlert($subscription, $highestPrice))->onQueue('default'));

                    // Delete subscription after notifying
                    $subscription->delete();
                } catch (\Exception $e) {
                    logger()->error('Subscription ' . $subscription->id . ' notification failed: ' . $e->getMessage());
                    continue;
                }
            }
        }
    }
}
