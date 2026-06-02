<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    /**
     * @param  array{desired_delivery_date?: string|null, note?: string|null, items: array<int, array{product_id: int, quantity: int}>}  $payload
     */
    public function createForCustomer(Customer $customer, array $payload): Order
    {
        return DB::transaction(function () use ($customer, $payload) {
            $requestedItems = collect($payload['items']);
            $productIds = $requestedItems->pluck('product_id')->all();

            $products = Product::query()
                ->with(['customerProductPrices' => function ($query) use ($customer) {
                    $query->where('customer_id', $customer->id);
                }])
                ->whereIn('id', $productIds)
                ->where('is_active', true)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $order = Order::query()->create([
                'order_no' => 'TMP-'.Str::uuid(),
                'customer_id' => $customer->id,
                'status' => Order::STATUS_PENDING,
                'desired_delivery_date' => $payload['desired_delivery_date'] ?? null,
                'note' => $payload['note'] ?? null,
                'total_amount' => 0,
                'ordered_at' => now(),
            ]);

            $totalAmount = 0;

            foreach ($requestedItems as $requestedItem) {
                $product = $products->get($requestedItem['product_id']);
                $unitPrice = $product->customerProductPrices->first()?->price ?? $product->default_price;
                $quantity = $requestedItem['quantity'];
                $subtotal = $unitPrice * $quantity;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_code' => $product->code,
                    'product_name' => $product->name,
                    'unit' => $product->unit,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                ]);

                $totalAmount += $subtotal;
            }

            $order->update([
                'order_no' => $this->formatOrderNo($order),
                'total_amount' => $totalAmount,
            ]);

            return $order->load(['items.product'])->loadCount('items');
        });
    }

    private function formatOrderNo(Order $order): string
    {
        return sprintf('ORD-%s-%06d', $order->ordered_at->format('Ymd'), $order->id);
    }
}
