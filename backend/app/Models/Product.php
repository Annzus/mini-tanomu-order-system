<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'code',
        'name',
        'unit',
        'default_price',
        'stock_quantity',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_price' => 'integer',
            'stock_quantity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function customerProductPrices(): HasMany
    {
        return $this->hasMany(CustomerProductPrice::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
