<?php

namespace App\Services\Contracts;

interface PaymentGatewayInterface
{
    /**
     * @param int $userId
     * @param float $amount
     * @param string $currency
     * @return array
     * @throws \Exception
     */
    public function processPayment(int $userId, float $amount, string $currency): array;

    /**
     * @return bool
     */
    public function isAvailable(): bool;
}

