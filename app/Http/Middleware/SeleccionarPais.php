<?php

namespace App\Http\Middleware;

use Closure;

class SeleccionarPais
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(\Session::has('pais')){
            config([ 'app.pais' => \Session::get('pais') ]);
        }
        else {
            if(config('app.pais_default')){
                $pais = \App\Pais::find(config('app.pais_default'));
                config([ 'app.pais' => $pais->id ]);
            }
        }
        return $next($request);
    }
}