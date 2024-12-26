<?php

namespace App\Providers;

use App\Services\Discord;
use App\Services\Interfaces\IDiscord;
use App\Services\Interfaces\IPayment;
use App\Services\PaymentService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerItRestServices();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }

    private function registerItRestServices(): void
    {
        $this->app->singleton(IDiscord::class, function () {
            return new Discord();
        });

        $this->app->singleton(IPayment::class, function () {
            return new PaymentService();
        });
    }
}
