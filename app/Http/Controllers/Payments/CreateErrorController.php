<?php

namespace App\Http\Controllers\Payments;

use App\Http\Requests\CreatePaymentRequest;
use App\Services\Contracts\PaymentServiceInterface;
use App\Http\Controllers\Controller;
use App\Exceptions\PaymentException;
use App\Exceptions\GatewayUnavailableException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class CreateErrorController extends Controller
{
    protected PaymentServiceInterface $paymentService;

    public function __construct(PaymentServiceInterface $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function __invoke(CreatePaymentRequest $request)
    {
        try {
            $transaction = $this->paymentService->processPayment(
                $request->user_id,
                $request->input('amount'),
                $request->input('currency') ?? 'BRL'
            );

            return response()->json([
                'success' => true,
                'transaction' => $transaction
            ], 200);

        } catch (GatewayUnavailableException $e) {
            Log::error('Gateway error: ' . $e, ['user_id' => $request->user_id]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());

        } catch (PaymentException $e) {
            Log::error('Payment error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Payment failed'
            ], Response::HTTP_BAD_REQUEST);

        } catch (\Exception $e) {
            Log::critical($e);

            return response()->json([
                'success' => false,
                'error' => 'error'
            ]);
        }
    }
}
