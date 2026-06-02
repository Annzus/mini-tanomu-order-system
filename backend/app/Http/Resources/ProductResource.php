<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $customerPrice = $this->relationLoaded('customerProductPrices')
            ? $this->customerProductPrices->first()?->price
            : null;

        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'unit' => $this->unit,
            'price' => $customerPrice ?? $this->default_price,
            'default_price' => $this->default_price,
            'is_customer_price' => $customerPrice !== null,
            'stock_quantity' => $this->stock_quantity,
        ];
    }
}
