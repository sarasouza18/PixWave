<?php

namespace App\Jobs;

use App\Services\Contracts\PaymentServiceInterface;
use App\Exceptions\PaymentException;
use App\Exceptions\GatewayUnavailableException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $amount;
    protected $currency;
    protected $paymentService;

    /**
     * Número de tentativas antes de falhar.
     */
    public $tries = 5;

    /**
     * Tempo máximo de execução para cada tentativa (em segundos).
     */
    public $timeout = 120;

    /**
     * Cria uma nova Job.
     *
     * @return void
     */
    public function __construct($userId, $amount, $currency = 'BRL')
    {
        $this->userId = $userId;
        $this->amount = $amount;
        $this->currency = $currency;
    }

    /**
     * Lógica para processar o pagamento.
     *
     * @return void
     */
    public function handle(PaymentServiceInterface $paymentService)
    {
        try {
            Log::info('Processando pagamento para usuário', ['user_id' => $this->userId]);

            // Processar pagamento via serviço
            $transaction = $paymentService->processPayment(
                $this->userId,
                $this->amount,
                $this->currency
            );

            Log::info('Pagamento processado com sucesso', ['transaction_id' => $transaction->transaction_id]);

        } catch (GatewayUnavailableException $e) {
            Log::error('Nenhum gateway disponível no momento. Tentativa: ' . $this->attempts(), ['user_id' => $this->userId]);

            // Rejeitar a Job se o limite de tentativas foi atingido
            if ($this->attempts() >= $this->tries) {
                Log::critical('Falha ao processar pagamento após múltiplas tentativas', ['user_id' => $this->userId]);
                throw $e;  // Lançar a exceção para encerrar a Job
            }

            // Reposicionar a Job na fila para uma nova tentativa
            $this->release(60);  // Tenta novamente após 60 segundos

        } catch (PaymentException $e) {
            Log::error('Erro ao processar pagamento', ['user_id' => $this->userId, 'error' => $e->getMessage()]);

            // Verificar número de tentativas
            if ($this->attempts() >= $this->tries) {
                Log::critical('Falha ao processar pagamento após múltiplas tentativas', ['user_id' => $this->userId]);
                throw $e;
            }

            // Reposicionar a Job na fila
            $this->release(60);
        } catch (Exception $e) {
            Log::critical('Erro inesperado ao processar pagamento', ['user_id' => $this->userId, 'error' => $e->getMessage()]);

            if ($this->attempts() >= $this->tries) {
                throw $e;
            }

            // Reposicionar a Job em caso de erro inesperado
            $this->release(60);
        }
    }

    /**
     * Define o comportamento em caso de falha.
     *
     * @return void
     */
    public function failed(Exception $exception)
    {
        // Registrar no log e enviar notificações, se necessário
        Log::error('Job falhou após várias tentativas', ['user_id' => $this->userId, 'error' => $exception->getMessage()]);
        // Aqui você pode disparar eventos, enviar alertas, etc.
    }
}
