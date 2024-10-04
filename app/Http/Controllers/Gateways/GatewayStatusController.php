<?php

namespace App\Http\Controllers\Gateways;

use App\Repositories\Contracts\GatewayRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Exceptions\GatewayUnavailableException;
use Exception;

class GatewayStatusController extends Controller
{
    protected GatewayRepositoryInterface $gatewayRepository;

    public function __construct(GatewayRepositoryInterface $gatewayRepository)
    {
        $this->gatewayRepository = $gatewayRepository;
    }

    /**
     * Método mágico __invoke para verificar o status dos gateways
     */
    public function __invoke()
    {
        try {
            // Verificar gateways disponíveis
            $gateways = $this->gatewayRepository->getAvailableGateways();

            if ($gateways->isEmpty()) {
                throw new GatewayUnavailableException();
            }

            return response()->json([
                'success' => true,
                'gateways' => $gateways
            ], Response::HTTP_OK);
        } catch (GatewayUnavailableException $e) {
            Log::error('Nenhum gateway disponível', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        } catch (Exception $e) {
            Log::critical('Erro inesperado ao verificar gateways: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro inesperado ao verificar os gateways.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
