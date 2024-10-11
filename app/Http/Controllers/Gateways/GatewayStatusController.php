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

    public function __invoke()
    {
        try {
            $gateways = $this->gatewayRepository->getAvailableGateways();

            if ($gateways->isEmpty()) {
                throw new GatewayUnavailableException();
            }

            return response()->json([
                'success' => true,
                'gateways' => $gateways
            ], Response::HTTP_OK);
        } catch (GatewayUnavailableException $e) {
            Log::error('No gateway available', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        } catch (Exception $e) {
            Log::critical('Unexpected error while checking gateways: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Unexpected error while checking the gateways.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
