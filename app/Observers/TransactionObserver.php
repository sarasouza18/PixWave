<?php

namespace App\Observers;
namespace App\Observers;

use App\Services\PaymentChainHandler;
use App\Services\MercadoPagoService;
use App\Services\GerencianetService;
use App\Enums\PaymentStatus;
use App\Models\Transaction;
use App\Jobs\ProcessPaymentJob;
use Exception;

class TransactionObserver
{
    protected $paymentHandler;

    public function __construct(MercadoPagoService $mercadoPagoService, GerencianetService $gerencianetService)
    {
        // Inicializa o PaymentChainHandler com os gateways
        $this->paymentHandler = new PaymentChainHandler([
            $mercadoPagoService,   // Mercado Pago será tentado primeiro
            $gerencianetService,   // Gerencianet será o fallback
        ]);
    }

    /**
     * Método chamado quando uma transação é criada
     *
     * @param Transaction $transaction
     * @return void
     */
    public function created(Transaction $transaction)
    {
        try {
            // Marca a transação como 'PROCESSING' antes de iniciar o pagamento
            $transaction->status = PaymentStatus::PROCESSING->value;
            $transaction->save();

            // Processa o pagamento utilizando a cadeia de responsabilidade (Chain of Responsibility)
            $paymentResult = $this->paymentHandler->processPayment(
                $transaction->user_id,
                $transaction->amount,
                $transaction->currency
            );

            // Após o processamento, atualiza o status no banco de dados
            $transaction->status = PaymentStatus::PAID->value;  // Sucesso no pagamento
            $transaction->gateway_status = $paymentResult['status'];  // Status do gateway
            $transaction->gateway_provider = $paymentResult['provider'];  // Provedor de pagamento utilizado
            $transaction->save();

            // Opcional: Dispara uma job para processar outras operações pós-pagamento
            ProcessPaymentJob::dispatch($transaction);
        } catch (Exception $e) {
            // Em caso de falha no processamento, marca o status como 'FAILED'
            $transaction->status = PaymentStatus::FAILED->value;
            $transaction->save();

            // Log ou outra lógica de tratamento de erro
        }
    }
}
