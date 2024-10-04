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
    protected $options;

    public function __construct()
    {
        $this->options = [
            'client_id' => env('GERENCIANET_CLIENT_ID'),
            'client_secret' => env('GERENCIANET_CLIENT_SECRET'),
            'pix_cert' => env('GERENCIANET_CERTIFICATE_PATH'),
            'sandbox' => env('GERENCIANET_SANDBOX'), // true para sandbox, false para produção
        ];
    }

    /**
     * Processa o pagamento via Gerencianet
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
            // Dados do pagamento PIX
            $body = [
                'calendario' => ['expiracao' => 3600], // Expiração do PIX em 1 hora
                'devedor' => [
                    'cpf' => '12345678909',  // Substituir com CPF real
                    'nome' => 'Nome do Pagador',
                ],
                'valor' => [
                    'original' => number_format($amount, 2, '.', ''),
                ],
                'chave' => env('GERENCIANET_PIX_KEY'), // Chave PIX para recebimento
                'solicitacaoPagador' => 'Descrição do pagamento',
            ];

            $api = new Gerencianet($this->options);
            $pix = $api->pixCreateImmediateCharge([], $body);

            // Atualiza o status da transação no banco de dados com base no status do gateway
            $transaction = Transaction::where('user_id', $userId)->latest()->first();
            $transaction->status = PaymentStatus::PENDING->value; // Gerencianet cria cobranças pendentes por padrão
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

            throw new Exception('Erro no pagamento via Gerencianet: ' . $e->error_description);
        } catch (Exception $e) {
            $transaction = Transaction::where('user_id', $userId)->latest()->first();
            $transaction->status = PaymentStatus::FAILED->value;
            $transaction->save();

            throw new Exception('Erro inesperado: ' . $e->getMessage());
        }
    }

    public function isAvailable(): bool
    {
        return true; // Verificação simples de disponibilidade
    }
}


