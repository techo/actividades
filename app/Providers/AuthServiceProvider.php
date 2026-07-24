<?php

namespace App\Providers;

use App\Actividad;
use App\Campaign;
use App\Inscripcion;
use App\Policies\ActividadesPolicy;
use App\Policies\CampanaPolicy;
use App\Policies\InscripcionesPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
        Actividad::class => ActividadesPolicy::class,
        Inscripcion::class => InscripcionesPolicy::class,
        Campaign::class => CampanaPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Passport::routes();

        // TTL de tokens: antes eran efectivamente eternos (default ~1 año / sin expirar).
        // Un token filtrado ahora caduca; el refresh token permite renovarlo desde la app.
        Passport::tokensExpireIn(now()->addDays(30));
        Passport::refreshTokensExpireIn(now()->addDays(60));
        Passport::personalAccessTokensExpireIn(now()->addDays(30));

        //Definición de Gates

        Gate::define('accesoBackoffice', function ($user){
            return $user->hasPermissionTo('ver_backoffice');
        });
    }
}
