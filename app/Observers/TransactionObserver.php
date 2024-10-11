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
        $this->paymentHandler = new PaymentChainHandler([
            $mercadoPagoService,
            $gerencianetService,
        ]);
    }

    /**
     *
     * @param Transaction $transaction
     * @return void
     */
    public function created(Transaction $transaction)
    {
        try {
            $transaction->status = PaymentStatus::PROCESSING->value;
            $transaction->save();

            $paymentResult = $this->paymentHandler->processPayment(
                $transaction->user_id,
                $transaction->amount,
                $transaction->currency
            );

            $transaction->status = PaymentStatus::PAID->value;
            $transaction->gateway_status = $paymentResult['status'];
            $transaction->gateway_provider = $paymentResult['provider'];
            $transaction->save();

            ProcessPaymentJob::dispatch($transaction);
        } catch (Exception $e) {
            $transaction->status = PaymentStatus::FAILED->value;
            $transaction->save();

        }
    }
}
