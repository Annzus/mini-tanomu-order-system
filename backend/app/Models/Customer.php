<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
        'closing_time',
    ];

    protected function casts(): array
    {
        return [
            'closing_time' => 'datetime:H:i',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function customerProductPrices(): HasMany
    {
        return $this->hasMany(CustomerProductPrice::class);
    }
}
