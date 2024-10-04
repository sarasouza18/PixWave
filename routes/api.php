<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Payments\CreatePaymentController;
use App\Http\Controllers\Payments\PaymentHistoryController;
use App\Http\Controllers\Gateways\GatewayStatusController;
use App\Services\PaymentService;
use App\Repositories\TransactionRepository;
use App\Repositories\GatewayRepository;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {

    // Rota para criar um pagamento
    Route::post('/payments', CreatePaymentController::class);

    // Rota para exibir o histórico de transações
    Route::get('/payments/history/{userId}', PaymentHistoryController::class);

    // Rota para verificar o status dos gateways
    Route::get('/gateways/status', GatewayStatusController::class);
});
