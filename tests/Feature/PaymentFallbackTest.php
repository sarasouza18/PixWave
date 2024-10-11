<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Gateway;

class PaymentFallbackTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tests the payment gateway fallback.
     *
     * @return void
     */
    public function test_payment_fallback_to_second_gateway()
    {
        // Create a test user
        $user = User::factory()->create();

        // Create two gateways, one unavailable and the other available
        $gateway1 = Gateway::factory()->create(['name' => 'Mercado Pago', 'available' => false]);
        $gateway2 = Gateway::factory()->create(['name' => 'Gerencianet', 'available' => true]);

        // Simulate a POST request to create a payment
        $response = $this->actingAs($user)->postJson('/api/payments', [
            'amount' => 250.75,
            'currency' => 'BRL',
        ]);

        // Check if the response has the HTTP status 201 (created)
        $response->assertStatus(201);

        // Verify that the transaction was created in the database using the second gateway
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => 250.75,
            'currency' => 'BRL',
            'gateway_id' => $gateway2->id,  // Should use the second gateway
        ]);
    }
}
