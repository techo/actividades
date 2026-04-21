<?php

namespace App\Providers;

use App\Oficina;
use App\Services\OneSignal\OneSignalService;
use App\Services\Push\PushNotificationService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Providers\TelescopeServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        View::composer('*', function ($view) {
            if (Auth::check() && Auth::user()->hasRole('admin')) { // o el campo que uses
                $oficinas = Oficina::with('pais')
                    ->where('id_pais', Auth::user()->idPaisPermitido)
                    ->whereHas('equipos') 
                    ->get();
    
                $view->with('oficinasPais', $oficinas);
            }
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->isLocal()) {
            $this->app->register(TelescopeServiceProvider::class);
        }

        // OneSignalService como singleton: una sola instancia de Guzzle por request.
        $this->app->singleton(OneSignalService::class, function ($app) {
            return new OneSignalService();
        });

        // PushNotificationService depende de OneSignalService — el Container lo resuelve.
        $this->app->singleton(PushNotificationService::class, function ($app) {
            return new PushNotificationService($app->make(OneSignalService::class));
        });
    }
}
