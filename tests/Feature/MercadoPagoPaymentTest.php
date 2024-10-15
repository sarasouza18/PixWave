<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use MercadoPago\Payment;
use MercadoPago\SDK;

class MercadoPagoPaymentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test payment creation using Mercado Pago (mocked).
     */
    public function test_create_payment_with_mercado_pago_success()
    {
        SDK::shouldReceive('setAccessToken')->once();
        $paymentMock = Mockery::mock(Payment::class);
        $paymentMock->transaction_amount = 250.00;
        $paymentMock->status = 'approved';
        $paymentMock->payer = ['email' => 'test@example.com'];
        $paymentMock->point_of_interaction = [
            'transaction_data' => [
                'qr_code' => 'test_qr_code',
                'qr_code_base64' => 'test_qr_code_base64',
            ]
        ];
        $paymentMock->shouldReceive('save')->andReturn(true);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/payments', [
            'amount' => 250.00,
            'currency' => 'BRL',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => 250.00,
            'status' => 'paid'
        ]);
    }
}
