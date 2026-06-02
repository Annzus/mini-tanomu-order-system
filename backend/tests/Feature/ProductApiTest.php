<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerProductPrice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_customer_can_list_active_products_with_resolved_prices(): void
    {
        [$customer, $user] = $this->createCustomerUser();
        $customProduct = $this->createProduct(['default_price' => 1000]);
        $defaultProduct = $this->createProduct(['default_price' => 600]);
        $inactiveProduct = $this->createProduct(['is_active' => false]);

        CustomerProductPrice::query()->create([
            'customer_id' => $customer->id,
            'product_id' => $customProduct->id,
            'price' => 850,
        ]);

        $this
            ->actingAs($user, 'sanctum')
            ->getJson('/api/products')
            ->assertOk()
            ->assertJsonFragment([
                'code' => $customProduct->code,
                'price' => 850,
                'default_price' => 1000,
                'is_customer_price' => true,
            ])
            ->assertJsonFragment([
                'code' => $defaultProduct->code,
                'price' => 600,
                'default_price' => 600,
                'is_customer_price' => false,
            ])
            ->assertJsonMissing([
                'code' => $inactiveProduct->code,
            ]);
    }

    public function test_customer_can_fetch_active_product_detail(): void
    {
        [$customer, $user] = $this->createCustomerUser();
        $product = $this->createProduct(['default_price' => 1200]);

        CustomerProductPrice::query()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'price' => 990,
        ]);

        $this
            ->actingAs($user, 'sanctum')
            ->getJson("/api/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.code', $product->code)
            ->assertJsonPath('data.price', 990)
            ->assertJsonPath('data.default_price', 1200)
            ->assertJsonPath('data.is_customer_price', true);
    }

    public function test_inactive_product_detail_is_not_available(): void
    {
        [, $user] = $this->createCustomerUser();
        $product = $this->createProduct(['is_active' => false]);

        $this
            ->actingAs($user, 'sanctum')
            ->getJson("/api/products/{$product->id}")
            ->assertNotFound();
    }

    public function test_admin_cannot_access_customer_product_api(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin-'.Str::uuid().'@example.test',
            'role' => 'admin',
            'customer_id' => null,
        ]);

        $this
            ->actingAs($admin, 'sanctum')
            ->getJson('/api/products')
            ->assertForbidden()
            ->assertJsonPath('message', 'Forbidden.');
    }

    public function test_unauthenticated_user_cannot_access_products(): void
    {
        $this
            ->getJson('/api/products')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    /**
     * @return array{0: Customer, 1: User}
     */
    private function createCustomerUser(): array
    {
        $customer = Customer::query()->create([
            'code' => 'TEST-'.Str::upper(Str::random(8)),
            'name' => 'Test Customer',
            'address' => 'Tokyo',
            'phone' => '03-0000-0000',
            'closing_time' => '17:00:00',
        ]);

        $user = User::factory()->create([
            'email' => 'customer-'.Str::uuid().'@example.test',
            'role' => 'customer',
            'customer_id' => $customer->id,
        ]);

        return [$customer, $user];
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createProduct(array $overrides = []): Product
    {
        return Product::query()->create($overrides + [
            'code' => 'TP-'.Str::upper(Str::random(10)),
            'name' => 'Test Product',
            'unit' => 'kg',
            'default_price' => 500,
            'stock_quantity' => 20,
            'is_active' => true,
        ]);
    }
}
