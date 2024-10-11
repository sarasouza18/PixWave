<?php

namespace App\Services;

use App\Services\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentStatus;
use Exception;
use Gerencianet\Exception\GerencianetException;
use Gerencianet\Gerencianet;
use App\Models\Transaction;

class GerenciaNetService implements PaymentGatewayInterface
{
    protected array $options;

    public function __construct()
    {
        $this->options = [
            'client_id' => env('GERENCIANET_CLIENT_ID'),
            'client_secret' => env('GERENCIANET_CLIENT_SECRET'),
            'pix_cert' => env('GERENCIANET_CERTIFICATE_PATH'),
            'sandbox' => env('GERENCIANET_SANDBOX'),
        ];
    }

    /**
     * Processes payment via Gerencianet
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
            $body = [
                'calendario' => ['expiracao' => 3600],
                'devedor' => [
                    'cpf' => '12345678909',
                    'nome' => 'Payer Name',
                ],
                'valor' => [
                    'original' => number_format($amount, 2, '.', ''),
                ],
                'chave' => env('GERENCIANET_PIX_KEY'),
                'solicitacaoPagador' => 'Payment description',
            ];

            $api = new Gerencianet($this->options);
            $pix = $api->pixCreateImmediateCharge([], $body);

            $transaction = Transaction::where('user_id', $userId)->latest()->first();
            $transaction->status = PaymentStatus::PENDING->value;
            $transaction->save();

            return [
                'status' => $transaction->status,
                'provider' => 'Gerencianet',
                'qr_code' => $pix['loc']['qrcode'],
                'qr_code_url' => $pix['loc']['imagemQrcode'],
            ];
        } catch (GerencianetException $e) {
            $transaction = Transaction::where('user_id', $userId)->latest()->first();
            $transaction->status = PaymentStatus::FAILED->value;
            $transaction->save();

            throw new Exception('Payment error via Gerencianet: ' . $e->error_description);
        } catch (Exception $e) {
            $transaction = Transaction::where('user_id', $userId)->latest()->first();
            $transaction->status = PaymentStatus::FAILED->value;
            $transaction->save();

            throw new Exception('Unexpected error: ' . $e->getMessage());
        }
    }

    public function isAvailable(): bool
    {
        return true;
    }
}
