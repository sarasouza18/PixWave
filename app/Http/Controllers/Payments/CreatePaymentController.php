<?php

namespace App\Http\Controllers\Payments;

use App\Http\Requests\CreatePaymentRequest;
use App\Services\Contracts\PaymentServiceInterface;
use App\Http\Controllers\Controller;
use App\Exceptions\PaymentException;
use App\Exceptions\GatewayUnavailableException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class CreatePaymentController extends Controller
{
    protected PaymentServiceInterface $paymentService;

    public function __construct(PaymentServiceInterface $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Método mágico __invoke para lidar com a solicitação de pagamento
     */
    public function __invoke(CreatePaymentRequest $request)
    {
        try {
            $transaction = $this->paymentService->processPayment(
                $request->input('user_id'),
                $request->input('amount'),
                $request->input('currency', 'BRL')
            );

            return response()->json([
                'success' => true,
                'transaction' => $transaction
            ], Response::HTTP_OK);
        } catch (GatewayUnavailableException $e) {
            Log::error('Erro de gateway: ' . $e->getMessage(), ['user_id' => $request->user_id]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        } catch (PaymentException $e) {
            Log::error('Erro de pagamento: ' . $e->getMessage(), ['user_id' => $request->user_id]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        } catch (\Exception $e) {
            Log::critical('Erro inesperado: ' . $e->getMessage(), ['user_id' => $request->user_id]);

            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro inesperado.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

