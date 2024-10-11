<?php

namespace App\Services;

use App\Jobs\ProcessPaymentJob;
use App\Repositories\TransactionRepository;
use App\Repositories\GatewayRepository;
use App\Services\Contracts\PaymentServiceInterface;
use Exception;
use Illuminate\Support\Facades\Redis;

class PaymentService implements PaymentServiceInterface
{
    protected TransactionRepository $transactionRepository;
    protected GatewayRepository $gatewayRepository;

    public function __construct(TransactionRepository $transactionRepository, GatewayRepository $gatewayRepository)
    {
        $this->transactionRepository = $transactionRepository;
        $this->gatewayRepository = $gatewayRepository;
    }

    /**
     * @throws Exception
     */
    public function processPayment($userId, $amount, $currency = 'BRL')
    {
        $availableGateway = $this->gatewayRepository->getAvailableGateways()->first();

        if (!$availableGateway) {
            throw new Exception('No payment gateway is available.');
        }

        $transaction = $this->transactionRepository->create([
            'user_id' => $userId,
            'amount' => $amount,
            'currency' => $currency,
            'gateway_id' => $availableGateway->id,
            'status' => 'pending',
            'type' => 'outgoing',
        ]);

        ProcessPaymentJob::dispatch($userId, $amount, $currency);

        return $transaction;
    }

    protected function selectGateway()
    {
        $gateways = $this->gatewayRepository->getAvailableGateways();
        foreach ($gateways as $gateway) {
            if (Redis::get('gateway:' . $gateway->id) === 'available') {
                return $gateway;
            }
        }

        return null;
    }

    protected function sendToGateway($gateway, $amount): bool
    {
        return true;
    }
}
