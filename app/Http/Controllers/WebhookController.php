<?php

namespace App\Http\Controllers;

use App\Services\AffiliateService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Pass the necessary data to the process order method
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->all();


        if (isset($payload['order_id'], $payload['subtotal_price'], $payload['merchant_domain'], $payload['discount_code'], $payload['customer_email'], $payload['customer_name'])) {

            $this->orderService->processOrder([
                'order_id' => $payload['order_id'],
                'subtotal_price' => $payload['subtotal_price'],
                'merchant_domain' => $payload['merchant_domain'],
                'discount_code' => $payload['discount_code'],
                'customer_email' => $payload['customer_email'],
                'customer_name' => $payload['customer_name'],
            ]);

            return response()->json(['message' => 'Order processed successfully'], 200);
        }

        return response()->json(['message' => 'Incomplete or invalid data in the webhook payload'], 400);
    }
}
