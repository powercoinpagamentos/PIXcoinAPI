<?php

namespace App\Providers;

use App\Services\Discord;
use App\Services\Interfaces\IDiscord;
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
    }
}
