<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\EquipoReunion;
use App\Http\Controllers\Controller;
use App\Http\Resources\EquipoReunionResource;
use App\Search\EquipoReunionesSearch;
use Illuminate\Http\Request;

use App\Http\Requests\Equipo\CrearEquipoReunion;

class EquipoReunionesController extends Controller
{
    public function index(Request $request, $idEquipo)
    {
        $filtros = [];
        $filtros['idEquipo'] = $idEquipo;
        
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

        $result = EquipoReunionesSearch::apply($filtros, $sort, $per_page);
        $reuniones = EquipoReunionResource::collection($result); // Yo se que es horrible pero no funciona sin esto
        return response()->json($result);
    }

    /**
     * Get a resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get($idEquipo, $idReunion)
    {
        $equipoReunion = EquipoReunion::with('personas')->findOrFail($idReunion);
        return new EquipoReunionResource($equipoReunion);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CrearEquipoReunion $request, $idEquipo)
    {
        $equipoReunion = new EquipoReunion();
        $validado = $request->validated();
        $equipoReunion->fill($validado);
        $equipoReunion->save();
        $equipoReunion->personas()->sync($request->input('personas', []));
        return response()->json($equipoReunion->fresh());

    }

    public function update(CrearEquipoReunion $request, $idEquipo, $idReunion)
    {
        $equipoReunion = EquipoReunion::findOrFail($idReunion);
        $validado = $validado = $request->validated();
        $equipoReunion->fill($validado);
        $equipoReunion->save();
        $equipoReunion->personas()->sync($request->input('personas', []));
        return response()->json($equipoReunion);
    }
}
