<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerProductPrice;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_customer_can_create_order_with_backend_calculated_price_snapshots(): void
    {
        [$customer, $user] = $this->createCustomerUser();
        $customProduct = $this->createProduct([
            'code' => 'TP-'.Str::upper(Str::random(10)),
            'name' => '特価商品',
            'default_price' => 1000,
        ]);
        $defaultProduct = $this->createProduct([
            'code' => 'TP-'.Str::upper(Str::random(10)),
            'name' => '通常商品',
            'default_price' => 600,
        ]);

        CustomerProductPrice::query()->create([
            'customer_id' => $customer->id,
            'product_id' => $customProduct->id,
            'price' => 850,
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/orders', [
                'desired_delivery_date' => now()->addDay()->toDateString(),
                'note' => '午前中希望',
                'items' => [
                    ['product_id' => $customProduct->id, 'quantity' => 2, 'unit_price' => 1],
                    ['product_id' => $defaultProduct->id, 'quantity' => 3],
                ],
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.status', Order::STATUS_PENDING)
            ->assertJsonPath('data.total_amount', 3500)
            ->assertJsonPath('data.items_count', 2)
            ->assertJsonPath('data.items.0.product_code', $customProduct->code)
            ->assertJsonPath('data.items.0.unit_price', 850)
            ->assertJsonPath('data.items.0.quantity', 2)
            ->assertJsonPath('data.items.0.subtotal', 1700)
            ->assertJsonPath('data.items.1.product_code', $defaultProduct->code)
            ->assertJsonPath('data.items.1.unit_price', 600)
            ->assertJsonPath('data.items.1.quantity', 3)
            ->assertJsonPath('data.items.1.subtotal', 1800);

        $orderId = $response->json('data.id');
        $orderNo = $response->json('data.order_no');

        $this->assertStringStartsWith('ORD-', $orderNo);
        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'customer_id' => $customer->id,
            'total_amount' => 3500,
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $orderId,
            'product_id' => $customProduct->id,
            'product_name' => '特価商品',
            'unit_price' => 850,
            'quantity' => 2,
            'subtotal' => 1700,
        ]);
    }

    public function test_order_detail_keeps_price_snapshot_after_product_price_changes(): void
    {
        [$customer, $user] = $this->createCustomerUser();
        $product = $this->createProduct(['default_price' => 500]);
        $originalProductName = $product->name;

        $createResponse = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/orders', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 4],
                ],
            ])
            ->assertCreated();

        $product->update([
            'name' => '変更後商品名',
            'default_price' => 999,
        ]);

        $this
            ->actingAs($user, 'sanctum')
            ->getJson('/api/orders/'.$createResponse->json('data.id'))
            ->assertOk()
            ->assertJsonPath('data.total_amount', 2000)
            ->assertJsonPath('data.items.0.product_name', $originalProductName)
            ->assertJsonPath('data.items.0.unit_price', 500)
            ->assertJsonPath('data.items.0.subtotal', 2000);
    }

    public function test_customer_can_list_only_own_orders(): void
    {
        [$customer, $user] = $this->createCustomerUser();
        [$otherCustomer, $otherUser] = $this->createCustomerUser();
        $product = $this->createProduct();

        $ownOrderId = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/orders', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1],
                ],
            ])
            ->assertCreated()
            ->json('data.id');

        $otherOrderId = $this
            ->actingAs($otherUser, 'sanctum')
            ->postJson('/api/orders', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 2],
                ],
            ])
            ->assertCreated()
            ->json('data.id');

        $this
            ->actingAs($user, 'sanctum')
            ->getJson('/api/orders')
            ->assertOk()
            ->assertJsonFragment(['id' => $ownOrderId])
            ->assertJsonMissing(['id' => $otherOrderId]);

        $this->assertDatabaseHas('orders', [
            'id' => $ownOrderId,
            'customer_id' => $customer->id,
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $otherOrderId,
            'customer_id' => $otherCustomer->id,
        ]);
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        [, $user] = $this->createCustomerUser();
        [, $otherUser] = $this->createCustomerUser();
        $product = $this->createProduct();

        $otherOrderId = $this
            ->actingAs($otherUser, 'sanctum')
            ->postJson('/api/orders', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1],
                ],
            ])
            ->assertCreated()
            ->json('data.id');

        $this
            ->actingAs($user, 'sanctum')
            ->getJson("/api/orders/{$otherOrderId}")
            ->assertNotFound();
    }

    public function test_inactive_products_cannot_be_ordered(): void
    {
        [, $user] = $this->createCustomerUser();
        $inactiveProduct = $this->createProduct(['is_active' => false]);

        $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/orders', [
                'items' => [
                    ['product_id' => $inactiveProduct->id, 'quantity' => 1],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['items.0.product_id']);
    }

    public function test_admin_cannot_access_customer_order_api(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin-'.Str::uuid().'@example.test',
            'role' => 'admin',
            'customer_id' => null,
        ]);

        $this
            ->actingAs($admin, 'sanctum')
            ->getJson('/api/orders')
            ->assertForbidden()
            ->assertJsonPath('message', 'Forbidden.');
    }

    public function test_unauthenticated_user_cannot_access_orders(): void
    {
        $this
            ->getJson('/api/orders')
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
