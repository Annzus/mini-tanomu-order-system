<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_customer_can_login_fetch_current_user_and_logout(): void
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

        $loginResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $loginResponse
            ->assertOk()
            ->assertJsonPath('user.email', $user->email)
            ->assertJsonPath('user.role', 'customer')
            ->assertJsonPath('user.customer.code', $customer->code)
            ->assertJsonStructure(['token']);

        $token = $loginResponse->json('token');

        $this
            ->withToken($token)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('user.email', $user->email);

        $this
            ->withToken($token)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Logged out.');

        $this->app['auth']->forgetGuards();

        $this
            ->withToken($token)
            ->getJson('/api/me')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_admin_can_login_without_customer_payload(): void
    {
        $user = User::factory()->create([
            'email' => 'admin-'.Str::uuid().'@example.test',
            'role' => 'admin',
            'customer_id' => null,
        ]);

        $this
            ->postJson('/api/login', [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertOk()
            ->assertJsonPath('user.email', $user->email)
            ->assertJsonPath('user.role', 'admin')
            ->assertJsonPath('user.customer', null)
            ->assertJsonStructure(['token']);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $user = User::factory()->create([
            'email' => 'invalid-login-'.Str::uuid().'@example.test',
            'role' => 'admin',
            'customer_id' => null,
        ]);

        $this
            ->postJson('/api/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Invalid credentials.')
            ->assertJsonValidationErrors(['email']);
    }

    public function test_unauthenticated_api_request_returns_json_without_accept_header(): void
    {
        $this
            ->get('/api/me')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    }
}
