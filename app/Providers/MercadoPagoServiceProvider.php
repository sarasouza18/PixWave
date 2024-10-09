<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use MercadoPago;

class MercadoPagoServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Inicializa o Mercado Pago com o Access Token do arquivo .env
        if (env('MERCADOPAGO_ACCESS_TOKEN') != null) {
            MercadoPago\SDK::setAccessToken(env('MERCADOPAGO_ACCESS_TOKEN'));
        }
        if (!App::runningInConsole()) {
            MercadoPago\SDK::setAccessToken(env('MERCADOPAGO_ACCESS_TOKEN'));
        }
    }

    public function boot()
    {
        //
    }
}
