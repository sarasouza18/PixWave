<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Jobs\ProcessPaymentJob;

class TransactionObserver
{
    /**
     * Método que será chamado quando uma nova transação for criada.
     */
    public function created(Transaction $transaction)
    {
        // Disparar a Job de processamento de pagamento
        ProcessPaymentJob::dispatch($transaction->user_id, $transaction->amount, $transaction->currency);
    }
}
