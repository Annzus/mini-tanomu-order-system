<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $customerId = request()->user()->customer_id;

        $products = Product::query()
            ->with(['customerProductPrices' => function ($query) use ($customerId) {
                $query->where('customer_id', $customerId);
            }])
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return ProductResource::collection($products);
    }

    public function show(Product $product): ProductResource
    {
        abort_if(! $product->is_active, 404);

        $customerId = request()->user()->customer_id;

        $product->load(['customerProductPrices' => function ($query) use ($customerId) {
            $query->where('customer_id', $customerId);
        }]);

        return new ProductResource($product);
    }
}
