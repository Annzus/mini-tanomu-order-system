<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminOrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = Order::query()
            ->with('customer')
            ->withCount('items')
            ->when($request->query('status'), function ($query, string $status) {
                $query->where('status', $status);
            })
            ->latest('ordered_at')
            ->get();

        return OrderResource::collection($orders);
    }

    public function show(Order $order): OrderResource
    {
        $order->load(['customer', 'items.product'])->loadCount('items');

        return new OrderResource($order);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): OrderResource
    {
        $order->update([
            'status' => $request->validated('status'),
        ]);

        $order->load(['customer', 'items.product'])->loadCount('items');

        return new OrderResource($order);
    }

    public function export(): StreamedResponse
    {
        $orders = Order::query()
            ->with(['customer', 'items'])
            ->latest('ordered_at')
            ->get();

        return response()->streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'order_no',
                'status',
                'customer_code',
                'customer_name',
                'desired_delivery_date',
                'ordered_at',
                'product_code',
                'product_name',
                'unit',
                'unit_price',
                'quantity',
                'subtotal',
                'total_amount',
            ]);

            foreach ($orders as $order) {
                foreach ($order->items as $item) {
                    fputcsv($handle, [
                        $order->order_no,
                        $order->status,
                        $order->customer->code,
                        $order->customer->name,
                        $order->desired_delivery_date?->toDateString(),
                        $order->ordered_at?->format('Y-m-d H:i'),
                        $item->product_code,
                        $item->product_name,
                        $item->unit,
                        $item->unit_price,
                        $item->quantity,
                        $item->subtotal,
                        $order->total_amount,
                    ]);
                }
            }

            fclose($handle);
        }, 'orders.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
