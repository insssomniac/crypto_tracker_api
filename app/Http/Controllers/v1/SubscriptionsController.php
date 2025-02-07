<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteSubscriptionsRequest;
use App\Http\Requests\SubscriptionRequest;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;

class SubscriptionsController extends Controller
{
    public function store(SubscriptionRequest $request): JsonResponse
    {
        $subscription = Subscription::create($request->all());

        return response()->json($subscription);
    }

    public function destroy(DeleteSubscriptionsRequest $request): JsonResponse
    {
        Subscription::byEmail($request->email)->delete();

        return response()->json(null, 204);
    }
}

