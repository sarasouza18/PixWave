<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\MercadoPagoService;
use App\Models\Transaction;
use App\Enums\PaymentStatus;
use Mockery;
use MercadoPago\Payment;
use MercadoPago\SDK;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MercadoPagoServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_process_payment_with_mercado_pago_success()
    {
        SDK::shouldReceive('setAccessToken')->once();
        $paymentMock = Mockery::mock(Payment::class);
        $paymentMock->transaction_amount = 100.00;
        $paymentMock->status = 'approved';
        $paymentMock->payer = ['email' => 'test@example.com'];
        $paymentMock->save = true;

        $mercadoPagoService = new MercadoPagoService();
        $response = $mercadoPagoService->processPayment(1, 100.00, 'BRL');

        $transaction = Transaction::latest()->first();
        $this->assertEquals(PaymentStatus::PAID->value, $transaction->status);
        $this->assertArrayHasKey('qr_code', $response);
    }

    public function test_process_payment_with_mercado_pago_failure()
    {
        SDK::shouldReceive('setAccessToken')->once();
        $paymentMock = Mockery::mock(Payment::class);
        $paymentMock->transaction_amount = 100.00;
        $paymentMock->status = 'rejected';
        $paymentMock->payer = ['email' => 'test@example.com'];
        $paymentMock->save = false;

        $mercadoPagoService = new MercadoPagoService();

        $this->expectException(\Exception::class);
        $mercadoPagoService->processPayment(1, 100.00, 'BRL');

        $transaction = Transaction::latest()->first();
        $this->assertEquals(PaymentStatus::FAILED->value, $transaction->status);
    }
}
