<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\GerenciaNetService;
use App\Models\Transaction;
use App\Enums\PaymentStatus;
use Mockery;
use Gerencianet\Gerencianet;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GerenciaNetServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_process_payment_with_gerencianet_success()
    {
        $gerencianetMock = Mockery::mock(Gerencianet::class);
        $gerencianetMock->shouldReceive('pixCreateImmediateCharge')->andReturn([
            'loc' => [
                'qrcode' => 'fake_qr_code',
                'imagemQrcode' => 'fake_image_qr_code'
            ]
        ]);

        $gerenciaNetService = new GerenciaNetService();
        $response = $gerenciaNetService->processPayment(1, 100.00, 'BRL');

        $transaction = Transaction::latest()->first();
        $this->assertEquals(PaymentStatus::PENDING->value, $transaction->status);
        $this->assertArrayHasKey('qr_code', $response);
    }

    public function test_process_payment_with_gerencianet_failure()
    {
        $gerencianetMock = Mockery::mock(Gerencianet::class);
        $gerencianetMock->shouldReceive('pixCreateImmediateCharge')->andThrow(new \Exception('API Error'));

        $this->expectException(\Exception::class);

        $gerenciaNetService = new GerenciaNetService();
        $gerenciaNetService->processPayment(1, 100.00, 'BRL');

        // Assertions
        $transaction = Transaction::latest()->first();
        $this->assertEquals(PaymentStatus::FAILED->value, $transaction->status);
    }
}
