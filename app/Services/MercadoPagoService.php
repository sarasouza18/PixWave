<?php

namespace App\Services;

use App\Services\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentStatus;
use Exception;
use MercadoPago\SDK;
use MercadoPago\Payment;
use App\Models\Transaction;

class MercadoPagoService implements PaymentGatewayInterface
{
    public function __construct()
    {
        SDK::setAccessToken(env('MERCADOPAGO_ACCESS_TOKEN'));
    }

    /**
     * Processes payment via Mercado Pago
     *
     * @param int $userId
     * @param float $amount
     * @param string $currency
     * @return array
     * @throws Exception
     */
    public function processPayment(int $userId, float $amount, string $currency): array
    {
        try {
            $payment = new Payment();
            $payment->transaction_amount = $amount;
            $payment->description = "PIX Payment - User: $userId";
            $payment->payment_method_id = "pix";
            $payment->payer = [
                "email" => "useremail@example.com"
            ];

            $payment->save();

            $transaction = Transaction::where('user_id', $userId)->latest()->first();
            if ($payment->status == 'approved') {
                $transaction->status = PaymentStatus::PAID->value;
            } elseif ($payment->status == 'pending') {
                $transaction->status = PaymentStatus::PENDING->value;
            } else {
                $transaction->status = PaymentStatus::FAILED->value;
            }

            $transaction->save();

            return [
                'status' => $transaction->status,
                'provider' => 'Mercado Pago',
                'qr_code' => $payment->point_of_interaction->transaction_data->qr_code,
                'qr_code_base64' => $payment->point_of_interaction->transaction_data->qr_code_base64,
            ];
        } catch (Exception $e) {
            $transaction = Transaction::where('user_id', $userId)->latest()->first();
            $transaction->status = PaymentStatus::FAILED->value;
            $transaction->save();

            throw new Exception('Payment error via Mercado Pago: ' . $e->getMessage());
        }
    }

    public function isAvailable(): bool
    {
        return true;
    }
}
