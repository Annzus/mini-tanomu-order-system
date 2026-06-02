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
                'name' => '札幌レストランA',
                'address' => '札幌市',
                'phone' => '011-0000-0000',
                'closing_time' => '17:00:00',
            ],
        );

        $customerB = Customer::query()->updateOrCreate(
            ['code' => 'CUST-B'],
            [
                'name' => '東京カフェB',
                'address' => '東京都',
                'phone' => '03-0000-0000',
                'closing_time' => '16:00:00',
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => '管理者ユーザー',
                'password' => 'password',
                'role' => 'admin',
                'customer_id' => null,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'customer-a@example.com'],
            [
                'name' => '得意先A 担当者',
                'password' => 'password',
                'role' => 'customer',
                'customer_id' => $customerA->id,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'customer-b@example.com'],
            [
                'name' => '得意先B 担当者',
                'password' => 'password',
                'role' => 'customer',
                'customer_id' => $customerB->id,
            ],
        );

        $products = [
            ['code' => 'P-001', 'name' => 'トマト', 'unit' => 'kg', 'default_price' => 500, 'stock_quantity' => 100],
            ['code' => 'P-002', 'name' => '玉ねぎ', 'unit' => 'kg', 'default_price' => 300, 'stock_quantity' => 100],
            ['code' => 'P-003', 'name' => 'じゃがいも', 'unit' => 'kg', 'default_price' => 250, 'stock_quantity' => 100],
            ['code' => 'P-004', 'name' => '牛肉', 'unit' => 'kg', 'default_price' => 2200, 'stock_quantity' => 40],
            ['code' => 'P-005', 'name' => '鶏肉', 'unit' => 'kg', 'default_price' => 900, 'stock_quantity' => 60],
            ['code' => 'P-006', 'name' => '米', 'unit' => '袋', 'default_price' => 3500, 'stock_quantity' => 50],
            ['code' => 'P-007', 'name' => 'ビール', 'unit' => 'ケース', 'default_price' => 4800, 'stock_quantity' => 30],
            ['code' => 'P-008', 'name' => 'ペーパータオル', 'unit' => 'パック', 'default_price' => 400, 'stock_quantity' => 120],
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
