<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Contracts\PaymentServiceInterface;
use App\Services\PaymentService;

class PaymentServiceServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(PaymentServiceInterface::class, PaymentService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}

