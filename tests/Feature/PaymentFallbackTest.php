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
     * Testa o fallback de gateways de pagamento.
     *
     * @return void
     */
    public function test_payment_fallback_to_second_gateway()
    {
        // Cria um usuário fictício
        $user = User::factory()->create();

        // Cria dois gateways, um indisponível e outro disponível
        $gateway1 = Gateway::factory()->create(['name' => 'Mercado Pago', 'avaliable' => false]);
        $gateway2 = Gateway::factory()->create(['name' => 'Gerencianet', 'available' => true]);

        // Simula uma requisição POST para criar um pagamento
        $response = $this->actingAs($user)->postJson('/api/payments', [
            'amount' => 250.75,
            'currency' => 'BRL',
        ]);

        // Verifica se a resposta tem o status HTTP 201 (criado)
        $response->assertStatus(201);

        // Verifica se a transação foi criada no banco de dados com o segundo gateway
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => 250.75,
            'currency' => 'BRL',
            'gateway_id' => $gateway2->id,  // Deve usar o segundo gateway
        ]);
    }
}

