<?php

namespace App\Http\Middleware;

use App\Actividad;  //country model
use Closure;
use Request;
use Route;

class SeleccionarPaisActividad
{
   /**
    * Handle an incoming request.
    *
    * @param \Illuminate\Http\Request $request
    * @param \Closure                 $next
    *
    * @return mixed
    */
   public function handle($request, Closure $next)
   {
       $idActividad = $request->route('id');  //get country part from url
       $actividad = Actividad::findOrFail($idActividad);
       config([ 'app.pais' => $actividad->pais->id ]);

       return $next($request);
   }
}