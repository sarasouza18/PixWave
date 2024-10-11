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

    public $tries = 5;

    public $timeout = 120;

    public function __construct($userId, $amount, $currency = 'BRL')
    {
        $this->userId = $userId;
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function handle(PaymentServiceInterface $paymentService)
    {
        try {
            Log::info('Processing payment for user', ['user_id' => $this->userId]);

            // Process payment via service
            $transaction = $paymentService->processPayment(
                $this->userId,
                $this->amount,
                $this->currency
            );

            Log::info('Payment successfully processed', ['transaction_id' => $transaction->transaction_id]);

        } catch (GatewayUnavailableException $e) {
            Log::error('No gateway available at the moment. Attempt: ' . $this->attempts(), ['user_id' => $this->userId]);

            if ($this->attempts() >= $this->tries) {
                Log::critical('Failed to process payment after multiple attempts', ['user_id' => $this->userId]);
                throw $e;
            }

            $this->release(60);

        } catch (PaymentException $e) {
            Log::error('Error processing payment', ['user_id' => $this->userId, 'error' => $e->getMessage()]);

            if ($this->attempts() >= $this->tries) {
                Log::critical('Failed to process payment after multiple attempts', ['user_id' => $this->userId]);
                throw $e;
            }

            $this->release(60);
        } catch (Exception $e) {
            Log::critical('Unexpected error while processing payment', ['user_id' => $this->userId, 'error' => $e->getMessage()]);

            if ($this->attempts() >= $this->tries) {
                throw $e;
            }

            $this->release(60);
        }
    }

    public function failed(Exception $exception)
    {
        Log::error('Job failed after multiple attempts', ['user_id' => $this->userId, 'error' => $exception->getMessage()]);
    }
}
