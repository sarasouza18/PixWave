<?php

namespace App\Services;

use Exception;

class PaymentChainHandler
{
    protected array $gateways = [];

    public function __construct(array $gateways)
    {
        $this->gateways = $gateways;
    }

    /**
     * @throws Exception
     */
    public function processPayment($userId, $amount, $currency): array
    {
        foreach ($this->gateways as $gateway) {
            if ($gateway->isAvailable()) {
                try {
                    return $gateway->processPayment($userId, $amount, $currency);
                } catch (Exception $e) {
                    continue;
                }
            }
        }

        throw new Exception('No payment gateway is available.');
    }
}
