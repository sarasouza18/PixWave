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
        $user = User::factory()->create();

        $gateway1 = Gateway::factory()->create(['name' => 'Mercado Pago', 'available' => false]);
        $gateway2 = Gateway::factory()->create(['name' => 'Gerencianet', 'available' => true]);

        $response = $this->actingAs($user)->postJson('/api/payments', [
            'amount' => 250.75,
            'currency' => 'BRL',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => 250.75,
            'currency' => 'BRL',
            'gateway_id' => $gateway2->id,
        ]);
    }
}
