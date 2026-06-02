<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminOrderApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_list_all_orders_with_customer_data(): void
    {
        $admin = $this->createAdminUser();
        [$customerA, $userA] = $this->createCustomerUser('A');
        [$customerB, $userB] = $this->createCustomerUser('B');
        $product = $this->createProduct();

        $orderAId = $this->createOrderForUser($userA, $product, 1);
        $orderBId = $this->createOrderForUser($userB, $product, 2);

        $this
            ->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/orders')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $orderAId,
                'name' => $customerA->name,
            ])
            ->assertJsonFragment([
                'id' => $orderBId,
                'name' => $customerB->name,
            ]);
    }

    public function test_admin_can_fetch_order_detail(): void
    {
        $admin = $this->createAdminUser();
        [$customer, $user] = $this->createCustomerUser();
        $product = $this->createProduct(['name' => '管理者確認商品']);
        $orderId = $this->createOrderForUser($user, $product, 3);

        $this
            ->actingAs($admin, 'sanctum')
            ->getJson("/api/admin/orders/{$orderId}")
            ->assertOk()
            ->assertJsonPath('data.customer.name', $customer->name)
            ->assertJsonPath('data.items.0.product_name', '管理者確認商品')
            ->assertJsonPath('data.items.0.quantity', 3);
    }

    public function test_admin_can_update_order_status_through_allowed_transition(): void
    {
        $admin = $this->createAdminUser();
        [, $user] = $this->createCustomerUser();
        $product = $this->createProduct();
        $orderId = $this->createOrderForUser($user, $product, 1);

        $this
            ->actingAs($admin, 'sanctum')
            ->patchJson("/api/admin/orders/{$orderId}/status", [
                'status' => Order::STATUS_CONFIRMED,
            ])
            ->assertOk()
            ->assertJsonPath('data.status', Order::STATUS_CONFIRMED);

        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'status' => Order::STATUS_CONFIRMED,
        ]);
    }

    public function test_admin_cannot_skip_invalid_status_transition(): void
    {
        $admin = $this->createAdminUser();
        [, $user] = $this->createCustomerUser();
        $product = $this->createProduct();
        $orderId = $this->createOrderForUser($user, $product, 1);

        $this
            ->actingAs($admin, 'sanctum')
            ->patchJson("/api/admin/orders/{$orderId}/status", [
                'status' => Order::STATUS_DELIVERED,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    public function test_admin_can_export_orders_as_csv_with_one_row_per_item(): void
    {
        $admin = $this->createAdminUser();
        [$customer, $user] = $this->createCustomerUser();
        $product = $this->createProduct([
            'code' => 'CSV-'.Str::upper(Str::random(8)),
            'name' => 'CSV商品',
            'default_price' => 700,
        ]);

        $orderId = $this->createOrderForUser($user, $product, 2);
        $order = Order::query()->findOrFail($orderId);

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->get('/api/admin/orders/export');

        $response->assertOk();

        $content = $response->streamedContent();

        $this->assertStringContainsString('order_no,status,customer_code,customer_name', $content);
        $this->assertStringContainsString($order->order_no, $content);
        $this->assertStringContainsString($customer->code, $content);
        $this->assertStringContainsString('CSV商品', $content);
        $this->assertStringContainsString(',700,2,1400,1400', $content);
    }

    public function test_customer_cannot_access_admin_order_api(): void
    {
        [, $user] = $this->createCustomerUser();

        $this
            ->actingAs($user, 'sanctum')
            ->getJson('/api/admin/orders')
            ->assertForbidden()
            ->assertJsonPath('message', 'Forbidden.');
    }

    private function createAdminUser(): User
    {
        return User::factory()->create([
            'email' => 'admin-'.Str::uuid().'@example.test',
            'role' => 'admin',
            'customer_id' => null,
        ]);
    }

    /**
     * @return array{0: Customer, 1: User}
     */
    private function createCustomerUser(string $suffix = ''): array
    {
        $customer = Customer::query()->create([
            'code' => 'TEST'.$suffix.'-'.Str::upper(Str::random(8)),
            'name' => 'Test Customer '.$suffix,
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

    private function createOrderForUser(User $user, Product $product, int $quantity): int
    {
        return $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/orders', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => $quantity],
                ],
            ])
            ->assertCreated()
            ->json('data.id');
    }
}
