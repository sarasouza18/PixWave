<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use MercadoPago;

class MercadoPagoServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Inicializa o Mercado Pago com o Access Token do arquivo .env
        MercadoPago\SDK::setAccessToken(env('MERCADOPAGO_ACCESS_TOKEN'));
    }

    public function boot()
    {
        //
    }
}
