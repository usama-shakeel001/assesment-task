<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {}

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {

        $merchant = Merchant::where('domain', $data['merchant_domain'])->first();

        $existingOrder = Order::where('order_id', $data['order_id'])->exists();
        if ($existingOrder) {
            return;
        }

        $user = User::firstOrCreate(['email' => $data['customer_email']], ['name' => $data['customer_name']]);

        $affiliate = $user->affiliate;

        if (!$affiliate) {

            $affiliate = $this->affiliateService->register($merchant, $data['customer_email'], $data['customer_name'], $merchant->default_commission_rate);
        }

        $orderData = [
            'order_id' => $data['order_id'],
            'subtotal' => $data['subtotal_price'],
            'merchant_id' => $merchant->id,
            'affiliate_id' => $affiliate->id,
            'discount_code' => $data['discount_code'],
            'customer_email' => $data['customer_email'],
        ];

        Order::create($orderData);
    }
}
