<?php

namespace App\Http\Middleware;

use App\Pais;  //country model
use Closure;
use Request;
use Route;

class PaisMiddleware
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
       $countryShortcode = $request->route('pais');  //get country part from url
       $country = Pais::where('abreviacion', '=', $countryShortcode)->where('habilitado', '=', 1)->first();
       if ($country === null) {
           return redirect('/actividades');
       }
       config([ 'app.pais' => $country->id ]);
       $request->session()->put('pais', $country->id);
       $request->session()->put('locale',$country->locale);

       return $next($request);
   }
}