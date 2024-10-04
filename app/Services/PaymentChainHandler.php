<?php

namespace App\Services;

use Exception;

class PaymentChainHandler
{
    protected $gateways = [];

    public function __construct(array $gateways)
    {
        $this->gateways = $gateways;
    }

    public function processPayment($userId, $amount, $currency): array
    {
        foreach ($this->gateways as $gateway) {
            if ($gateway->isAvailable()) {
                try {
                    return $gateway->processPayment($userId, $amount, $currency);
                } catch (Exception $e) {
                    // Log o erro ou execute alguma ação caso o pagamento falhe
                    continue;  // Tenta o próximo gateway
                }
            }
        }

        throw new Exception('Nenhum gateway de pagamento está disponível.');
    }
}
