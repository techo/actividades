<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\Controller;
use App\Http\Resources\EquiposResource;
use App\Search\EquiposSearch;
use App\Actividad;
use App\Equipo;
use Illuminate\Http\Request;

class EquiposController extends Controller
{
    public function index(Request $request, $idOficina = null)
    {
        // Si viene idActividad, autorizar vía policy y usar la lógica de oficina
        // para que el coordinador de la actividad vea los equipos disponibles.
        $forzarPorOficina = false;
        if ($request->filled('idActividad')) {
            $actividad = Actividad::findOrFail($request->idActividad);
            $this->authorize('esCoordinadorOAdmin', $actividad);

            // Si no es admin, forzar el filtro por oficina (igual que admin pero sin
            // restringir por coordinadores_equipos propios del usuario)
            if (!auth()->user()->hasRole('admin')) {
                $forzarPorOficina = true;
            }
        }

        $filtros = [];
        if($request->has('equipo')){
            $filtros['equipo'] = $request->equipo;
        }
        $sort = 'created_at desc';

        if($request->filled('sort')) {
            if(strpos($request->sort, "|"))
                $sort = join(" ",explode("|", $request->sort));
            else
                $sort = $request->sort;
        }

        $per_page = 25;
        if($request->filled('per_page')) {
            $per_page = $request->per_page;
        }

        $result = EquiposSearch::apply($filtros, $sort, $per_page, null, $idOficina, $forzarPorOficina);
        $equipos = EquiposResource::collection($result); // Yo se que es horrible pero no funciona sin esto
        return response()->json($result);
    }

    
}
