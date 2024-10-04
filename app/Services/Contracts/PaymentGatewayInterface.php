<?php

namespace App\Services\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Processa o pagamento.
     *
     * @param int $userId
     * @param float $amount
     * @param string $currency
     * @return array
     * @throws \Exception
     */
    public function processPayment($userId, $amount, $currency): array;

    /**
     * Verifica a disponibilidade do gateway.
     *
     * @return bool
     */
    public function isAvailable(): bool;
}

