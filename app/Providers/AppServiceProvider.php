<?php

namespace App\Providers;

use App\Oficina;
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
                    ->where('id_pais', Auth::user()->idPais)
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
        //
        if ($this->app->isLocal()) {
        $this->app->register(TelescopeServiceProvider::class);
    }
    }
}
