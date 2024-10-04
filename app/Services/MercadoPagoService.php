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
        // Inicializa o SDK do Mercado Pago com o token de acesso
        SDK::setAccessToken(env('MERCADOPAGO_ACCESS_TOKEN'));
    }

    /**
     * Processa o pagamento via Mercado Pago
     *
     * @param int $userId
     * @param float $amount
     * @param string $currency
     * @return array
     * @throws Exception
     */
    public function processPayment($userId, $amount, $currency = 'BRL'): array
    {
        try {
            // Criação de um novo pagamento PIX via Mercado Pago
            $payment = new Payment();
            $payment->transaction_amount = $amount;
            $payment->description = "Pagamento via PIX - Usuário: $userId";
            $payment->payment_method_id = "pix"; // Método de pagamento PIX
            $payment->payer = [
                "email" => "emaildousuario@example.com" // Substituir com e-mail real
            ];

            $payment->save(); // Salva o pagamento e envia a requisição

            // Atualiza o status da transação no banco de dados com base no status do gateway
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
            // Atualiza o status como falha em caso de exceção
            $transaction = Transaction::where('user_id', $userId)->latest()->first();
            $transaction->status = PaymentStatus::FAILED->value;
            $transaction->save();

            throw new Exception('Erro no pagamento via Mercado Pago: ' . $e->getMessage());
        }
    }

    public function isAvailable(): bool
    {
        // Verificação básica de disponibilidade (pode ser mais complexa)
        return true;
    }
}

