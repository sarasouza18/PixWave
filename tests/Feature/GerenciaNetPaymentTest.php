<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Gerencianet\Gerencianet;

class GerenciaNetPaymentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test payment failure using Gerencianet (mocked).
     */
    public function test_create_payment_with_gerencianet_failure()
    {
        $gerencianetMock = Mockery::mock(Gerencianet::class);
        $gerencianetMock->shouldReceive('pixCreateImmediateCharge')
            ->andThrow(new \Exception('API Error'));

        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/payments', [
            'amount' => 150.00,
            'currency' => 'BRL',
        ]);

        $response->assertStatus(500);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => 150.00,
            'status' => 'failed'
        ]);
    }
}
