<?php

namespace App\Console\Commands;

use App\DataProviders\Bitfinex;
use App\Models\Subscription;
use App\Notifications\PercentChangeAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class HandlePercentChangeAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:handle-percent-change-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks Percent Change subscriptions and notifies customers';

    /**
     * Execute the console command.
     */
    public function handle(Bitfinex $dataProvider)
    {
        $symbols = Subscription::byAlertType(Subscription::ALERT_TYPE_PERCENT_CHANGE)->pluck('symbol')->unique();
        $intervals = Subscription::byAlertType(Subscription::ALERT_TYPE_PERCENT_CHANGE)->pluck('time_interval')->unique();

        foreach ($symbols as $symbol) {
            foreach ($intervals as $interval) {
                // Find percent of change for each symbol and interval
                $extremePrices = $dataProvider->getExtremesForPeriod($symbol, $interval);
                $percentChange = 100 - ($extremePrices['min'] / $extremePrices['max'] * 100);

                // Select only subscriptions which meets condition
                $subscriptionsToNotify = Subscription::byAlertType(Subscription::ALERT_TYPE_PERCENT_CHANGE)
                    ->where('time_interval', $interval)
                    ->where('percent_change', '<=', $percentChange)
                    ->get();

                // Notify subscribers
                foreach ($subscriptionsToNotify as $subscription) {
                    Notification::route('mail', $subscription->email)
                        ->notify(new PercentChangeAlert($subscription, $percentChange, $interval, $extremePrices['max'], $extremePrices['min']));

                    // Delete subscription after notifying
                    $subscription->delete();
                }
            }
        }
    }
}
