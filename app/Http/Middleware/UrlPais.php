<?php

namespace App\Http\Middleware;

use App\Pais;  //country model
use Closure;
use Request;
use Route;

class UrlPais
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
       $abreviacion = $request->route('abreviacion');
       $pais = Pais::where('abreviacion', '=', $abreviacion)->where('habilitado', '=', 1)->first();

       if ($pais == null) {
            if(config('app.pais_default')){
                $pais = Pais::where('id', config('app.pais_default'))->first();
                config([ 'app.pais' =>  $pais->id]);
                $request->session()->put('pais', $pais->id);
                $request->session()->put('locale',$pais->locale);
            }
       } else {
            config([ 'app.pais' => $pais->id ]);
            $request->session()->put('pais', $pais->id);
            $request->session()->put('locale',$pais->locale);
           // dd($pais->id);

       }

       return $next($request);
   }
}