<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\Controller;
use App\Http\Resources\EquipoPersonasResource;
use App\Search\EquipoPersonasSearch;
use App\EquipoPersonas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Requests\Equipo\CrearEquipoPersonas;
use App\Persona;
use Illuminate\Support\Facades\Log;

class EquipoPersonasController extends Controller
{
    public function index(Request $request)
    {
        $filtros = [];
        Log::info($request);
        if($request->has('filter')){
        Log::info("$request");
        $filtros['nombre'] = $request->filter;
        }
        
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

        $result = EquipoPersonasSearch::apply($filtros, $sort, $per_page);
        $equipos = EquipoPersonasResource::collection($result); // Yo se que es horrible pero no funciona sin esto
        return response()->json($result);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CrearEquipoPersonas $request, $idEquipo)
    {
        $equipoPersona = new EquipoPersonas();
        $validado = $request->validated();
        $persona = Persona::find($validado['idPersona']);
        $equipoPersona->fill($validado);
        $equipoPersona->idPersona = $persona->idPersona;

        $equipoPersona->save();

        return response()->json($equipoPersona->fresh());

    }
    
}
