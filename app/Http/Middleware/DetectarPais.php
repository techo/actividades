<?php

namespace App\Http\Middleware;

use Closure;

use App\Pais;

class DetectarPais
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
        $dominio = $request->getHttpHost();

        $codigo = explode('.', $dominio)[0];
        if(strlen($codigo)==2 && $pais=Pais::porCodigo($codigo)) {
            $protocolo = 'http';
            if($request->secure()) {
                $protocolo = 'https';
            }

            config(['app.url'=>$protocolo . '://' . $dominio]);
            config(['techo.pais'=>$pais->id, 'techo.nombre_pais'=>$pais->nombre]);
        }

        return $next($request);
    }
}
