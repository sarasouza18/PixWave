<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class GatewayUnavailableException extends Exception
{
    public function __construct($message = "Nenhum gateway disponível", $code = Response::HTTP_SERVICE_UNAVAILABLE)
    {
        parent::__construct($message, $code);
    }
}
