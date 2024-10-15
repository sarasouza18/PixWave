<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Gateway;

class CreatePaymentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa a criação de um pagamento via API.
     *
     * @return void
     */
    public function test_create_payment()
    {
        $user = User::factory()->create();

        $gateway = Gateway::factory()->create(['name' => 'Mercado Pago', 'available' => true]);

        $response = $this->actingAs($user)->postJson('/api/payments', [
            'amount' => 150.50,
            'currency' => 'BRL',
            'gateway_id' => $gateway->id
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => 150.50,
            'currency' => 'BRL',
            'gateway_id' => $gateway->id,
            'status' => 'pending'
        ]);

        $transaction = Transaction::latest()->first();
        $this->assertEquals($gateway->id, $transaction->gateway_id);
    }
}

