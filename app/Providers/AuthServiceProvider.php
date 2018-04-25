<?php

namespace App\Providers;

use App\Actividad;
use App\Policies\ActividadesPolicy;
use function foo\func;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

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
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //Definición de Gates

        Gate::define('accesoBackoffice', function ($user){
            return $user->hasPermissionTo('ver_backoffice');
        });
    }
}
