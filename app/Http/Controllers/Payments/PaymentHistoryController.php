<?php

namespace App\Http\Controllers\Payments;

use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentHistoryController extends Controller
{
    protected TransactionRepositoryInterface $transactionRepository;

    public function __construct(TransactionRepositoryInterface $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }
    
    public function __invoke(Request $request, $userId)
    {
        try {
            $transactions = $this->transactionRepository->getByUserId($userId);

            return response()->json([
                'success' => true,
                'transactions' => $transactions
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Error fetching transaction history: ' . $e->getMessage(), ['user_id' => $userId]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching transaction history.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
