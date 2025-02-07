<?php

namespace App\Http\Requests;

use App\Models\Subscription;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SubscriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'symbol' => 'required|string',
            'price_limit' => [
                'nullable',
                'numeric',
                'required_if:alert_type,==,' . Subscription::ALERT_TYPE_PRICE_ABOVE,
            ],
            'percent_change' => [
                'nullable',
                'numeric',
                'min:1',
                'max:100',
                'required_if:alert_type,==,' . Subscription::ALERT_TYPE_PERCENT_CHANGE,
            ],
            'time_interval' => [
                'nullable',
                'numeric',
                'min:1',
                'max:24',
                'required_if:alert_type,==,' . Subscription::ALERT_TYPE_PERCENT_CHANGE,
            ],
            'alert_type' => [
                'required',
                'string',
                'in:' . implode(',', Subscription::alertTypes())
            ],
        ];
    }
}
