<?php

namespace App\Providers;

use App\Oficina;
use App\Services\OneSignal\OneSignalService;
use App\Services\Push\PushNotificationService;
use Illuminate\Database\Eloquent\Relations\Relation;
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

        // En producción, generar siempre URLs https y asegurar el esquema seguro
        // (complementa SESSION_SECURE_COOKIE y el proxy/HSTS).
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Alias estables para las relaciones polimórficas de condiciones de
        // preguntas. Desacopla la BD de los namespaces PHP.
        Relation::morphMap([
            'actividad_pregunta' => \App\ActividadPregunta::class,
            'campaign_pregunta'  => \App\CampaignPregunta::class,
        ]);

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
