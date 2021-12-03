<?php

namespace App\Providers;

use App\Actividad;
use App\Inscripcion;
use App\Policies\ActividadesPolicy;
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
        //Definición de Gates

        Gate::define('accesoBackoffice', function ($user){
            return $user->hasPermissionTo('ver_backoffice');
        });
    }
}
