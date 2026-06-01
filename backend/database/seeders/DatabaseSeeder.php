<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerProductPrice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $customerA = Customer::query()->updateOrCreate(
            ['code' => 'CUST-A'],
            [
                'name' => 'Sapporo Restaurant A',
                'address' => 'Sapporo',
                'phone' => '011-0000-0000',
                'closing_time' => '17:00:00',
            ],
        );

        $customerB = Customer::query()->updateOrCreate(
            ['code' => 'CUST-B'],
            [
                'name' => 'Tokyo Cafe B',
                'address' => 'Tokyo',
                'phone' => '03-0000-0000',
                'closing_time' => '16:00:00',
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => 'password',
                'role' => 'admin',
                'customer_id' => null,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'customer-a@example.com'],
            [
                'name' => 'Customer A User',
                'password' => 'password',
                'role' => 'customer',
                'customer_id' => $customerA->id,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'customer-b@example.com'],
            [
                'name' => 'Customer B User',
                'password' => 'password',
                'role' => 'customer',
                'customer_id' => $customerB->id,
            ],
        );

        $products = [
            ['code' => 'P-001', 'name' => 'Tomato', 'unit' => 'kg', 'default_price' => 500, 'stock_quantity' => 100],
            ['code' => 'P-002', 'name' => 'Onion', 'unit' => 'kg', 'default_price' => 300, 'stock_quantity' => 100],
            ['code' => 'P-003', 'name' => 'Potato', 'unit' => 'kg', 'default_price' => 250, 'stock_quantity' => 100],
            ['code' => 'P-004', 'name' => 'Beef', 'unit' => 'kg', 'default_price' => 2200, 'stock_quantity' => 40],
            ['code' => 'P-005', 'name' => 'Chicken', 'unit' => 'kg', 'default_price' => 900, 'stock_quantity' => 60],
            ['code' => 'P-006', 'name' => 'Rice', 'unit' => 'bag', 'default_price' => 3500, 'stock_quantity' => 50],
            ['code' => 'P-007', 'name' => 'Beer', 'unit' => 'case', 'default_price' => 4800, 'stock_quantity' => 30],
            ['code' => 'P-008', 'name' => 'Paper Towel', 'unit' => 'pack', 'default_price' => 400, 'stock_quantity' => 120],
        ];

        $productsByCode = [];

        foreach ($products as $productData) {
            $product = Product::query()->updateOrCreate(
                ['code' => $productData['code']],
                $productData + ['is_active' => true],
            );

            $productsByCode[$product->code] = $product;
        }

        $customerPrices = [
            [$customerA, 'P-001', 450],
            [$customerA, 'P-004', 2100],
            [$customerB, 'P-007', 4500],
            [$customerB, 'P-008', 350],
        ];

        foreach ($customerPrices as [$customer, $productCode, $price]) {
            CustomerProductPrice::query()->updateOrCreate(
                [
                    'customer_id' => $customer->id,
                    'product_id' => $productsByCode[$productCode]->id,
                ],
                ['price' => $price],
            );
        }
    }
}
