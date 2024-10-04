<?php

namespace App\Services;

use App\Jobs\ProcessPaymentJob;
use App\Repositories\TransactionRepository;
use App\Repositories\GatewayRepository;
use App\Services\Contracts\PaymentServiceInterface;
use Illuminate\Support\Facades\Redis;

class PaymentService implements PaymentServiceInterface
{
    protected $transactionRepository;
    protected $gatewayRepository;

    public function __construct(TransactionRepository $transactionRepository, GatewayRepository $gatewayRepository)
    {
        $this->transactionRepository = $transactionRepository;
        $this->gatewayRepository = $gatewayRepository;
    }

    public function processPayment($userId, $amount, $currency = 'BRL')
    {
        // Lógica de escolha de gateway e verificação
        $availableGateway = $this->gatewayRepository->getAvailableGateways()->first();

        if (!$availableGateway) {
            throw new \Exception('Nenhum gateway disponível no momento.');
        }

        // Criação da transação antes de processar o pagamento
        $transaction = $this->transactionRepository->create([
            'user_id' => $userId,
            'amount' => $amount,
            'currency' => $currency,
            'gateway_id' => $availableGateway->id,
            'status' => 'pending',
            'type' => 'outgoing',
        ]);

        // Disparar a Job para processar o pagamento
        ProcessPaymentJob::dispatch($userId, $amount, $currency);

        return $transaction;
    }

    protected function selectGateway()
    {
        // Checa no Redis o status dos gateways
        $gateways = $this->gatewayRepository->getAvailableGateways();
        foreach ($gateways as $gateway) {
            if (Redis::get('gateway:' . $gateway->id) === 'available') {
                return $gateway;
            }
        }

        return null;
    }

    protected function sendToGateway($gateway, $amount)
    {
        // Simulação de integração com gateway
        // Substituir com a chamada real ao gateway (API de integração)
        return true;  // Sucesso simulado
    }
}
