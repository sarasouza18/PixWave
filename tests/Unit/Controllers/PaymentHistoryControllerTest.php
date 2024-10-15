<?php

namespace Tests\Unit\Controllers;

use Tests\TestCase;
use App\Http\Controllers\Payments\PaymentHistoryController;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mockery;

class PaymentHistoryControllerTest extends TestCase
{
    public function test_invoke_method_returns_transaction_history()
    {
        $transactionRepositoryMock = Mockery::mock(TransactionRepositoryInterface::class);
        $transactionRepositoryMock->shouldReceive('getByUserId')->andReturn([
            ['id' => 1, 'amount' => 100.00, 'status' => 'paid'],
            ['id' => 2, 'amount' => 200.00, 'status' => 'pending']
        ]);

        $controller = new PaymentHistoryController($transactionRepositoryMock);

        $request = Request::create('/transactions', 'GET');

        $response = $controller->__invoke($request, 1);

        $response->assertStatus(200);
        $this->assertArrayHasKey('transactions', $response->getData(true));
    }

    public function test_invoke_method_logs_error_on_failure()
    {
        $transactionRepositoryMock = Mockery::mock(TransactionRepositoryInterface::class);
        $transactionRepositoryMock->shouldReceive('getByUserId')->andThrow(new \Exception('Error fetching transactions'));

        $controller = new PaymentHistoryController($transactionRepositoryMock);

        Log::spy();

        $request = Request::create('/transactions', 'GET');

        $response = $controller->__invoke($request, 1);

        Log::shouldHaveReceived('error');

        $response->assertStatus(500);
        $this->assertEquals('Error fetching transactions.', $response->getData(true)['message']);
    }
}
