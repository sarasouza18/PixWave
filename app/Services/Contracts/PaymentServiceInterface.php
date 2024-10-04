<?php

namespace App\Services\Contracts;

interface PaymentServiceInterface
{
    public function processPayment($userId, $amount, $currency);
}

