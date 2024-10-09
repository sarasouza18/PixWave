<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Gateway;

class CreatePaymentTest extends TestCase
{
    use RefreshDatabase; // Isso limpa o banco de dados antes de cada teste

    /**
     * Testa a criação de um pagamento via API.
     *
     * @return void
     */
    public function test_create_payment()
    {
        // Cria um usuário fictício
        $user = User::factory()->create();

        // Cria um gateway fictício (Mercado Pago ou Gerencianet)
        $gateway = Gateway::factory()->create(['name' => 'Mercado Pago', 'available' => true]);

        // Simula uma requisição POST para criar um pagamento
        $response = $this->actingAs($user)->postJson('/api/payments', [
            'amount' => 150.50,
            'currency' => 'BRL',
            'gateway_id' => $gateway->id
        ]);

        // Verifica se a resposta tem o status HTTP 201 (criado)
        $response->assertStatus(201);

        // Verifica se a transação foi criada no banco de dados
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => 150.50,
            'currency' => 'BRL',
            'gateway_id' => $gateway->id,
            'status' => 'pending'
        ]);

        // Verifica se o pagamento foi criado com o gateway correto
        $transaction = Transaction::latest()->first();
        $this->assertEquals($gateway->id, $transaction->gateway_id);
    }
}

