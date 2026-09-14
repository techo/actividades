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

        // TTL de tokens. La app móvil autentica con PERSONAL ACCESS TOKENS
        // (PersonasController::login -> createToken()->accessToken), que NO llevan
        // refresh token: no hay forma de renovarlos en silencio. Por eso el que
        // manda para la app es personalAccessTokensExpireIn. Con 30 días la gente
        // quedaba deslogueada cada mes (~150 tokens vencían por día y cada uno
        // generaba ~2 401 "denied" por los reintentos de la app). Se sube a 60 días:
        // la mitad de vencimientos, sin ser tan largo como para debilitar A-5 (sigue
        // caducando). El kill switch de un token filtrado es la revocación: logout
        // (PersonasController::logout), reset de contraseña
        // (App\Traits\ResetsPasswords::resetPassword) y cambio de clave en el perfil
        // (ajax\UsuarioController::update) revocan los tokens de API. El TTL solo
        // aplica a tokens creados de ahora en más; los ya emitidos vencen a su fecha.
        // tokensExpireIn/refreshTokensExpireIn aplican a los grants OAuth
        // (authorization_code/password) que esta app NO usa; se dejan por si algún
        // consumidor futuro los necesita.
        Passport::tokensExpireIn(now()->addDays(30));
        Passport::refreshTokensExpireIn(now()->addDays(60));
        Passport::personalAccessTokensExpireIn(now()->addDays(60));

        //Definición de Gates

        Gate::define('accesoBackoffice', function ($user){
            return $user->hasPermissionTo('ver_backoffice');
        });
    }
}
