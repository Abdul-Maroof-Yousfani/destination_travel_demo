<?php

namespace App\Providers;

use App\Models\Client;
use App\Observers\ClientObserver;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\View\Components\SessionTimeoutContainer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Schema::defaultStringLength(250);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        DB::statement("SET time_zone='+05:00'");
        Client::observe(ClientObserver::class);
        Paginator::useBootstrapFive();
    }
}