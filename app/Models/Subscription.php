<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Subscription extends Model
{
    use HasFactory;
    use Notifiable;

    public const ALERT_TYPE_PRICE_ABOVE = 'priceAbove';
    public const ALERT_TYPE_PERCENT_CHANGE = 'percentChange';

    protected $table = 'subscriptions';

    protected $fillable = [
        'email',
        'symbol',
        'price_limit',
        'percent_change',
        'time_interval',
        'alert_type',
    ];

    public static function alertTypes()
    {
        return [
            self::ALERT_TYPE_PRICE_ABOVE,
            self::ALERT_TYPE_PERCENT_CHANGE,
        ];
    }

    public function scopeByEmail(Builder $query, string $email)
    {
        return $query->where('email', $email);
    }

    public function scopeByAlertType(Builder $query, string $alertType)
    {
        return $query->where('alert_type', $alertType);
    }
}
