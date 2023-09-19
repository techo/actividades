<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\Controller;
use App\Http\Resources\IntegranteResource;
use App\Search\IntegrantesSearch;
use App\Integrante;
use Illuminate\Http\Request;

use App\Http\Requests\Equipo\CrearIntegrante;
use App\Http\Requests\Equipo\DeleteIntegrante;
use App\Http\Requests\Equipo\GetIntegrante;
use App\Persona;

class IntegrantesController extends Controller
{
    public function index(Request $request, $idEquipo)
    {
        $filtros = [];
        if($request->has('filter')){
            $filtros['nombre'] = $request->filter;
        }


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

        $result = IntegrantesSearch::apply($filtros, $sort, $per_page);
        $equipos = IntegranteResource::collection($result); // Yo se que es horrible pero no funciona sin esto
        return response()->json($result);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CrearIntegrante $request, $idEquipo)
    {
        $integrate = new Integrante();
        $validado = $request->validated();
        $persona = Persona::find($validado['idPersona']);
        $integrate->fill($validado);
        $integrate->idPersona = $persona->idPersona;

        $integrate->save();

        return response()->json($integrate->fresh());

    }

    public function update(CrearIntegrante $request, $idEquipo, $idIntegrante)
    {
        $integrante = Integrante::findOrFail($idIntegrante);
        $validado = $validado = $request->validated();
        $integrante->fill($validado);
        $integrante->save();

        return response()->json($integrante);
    }

    /**
     * Get a resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get(GetIntegrante $request, $idEquipo, $idIntegrante)
    {
        $integrante = Integrante::findOrFail($idIntegrante);
        $r = $integrante->persona;
		$integrante->personaData = [
			"idPersona" => $r->idPersona,
			"nombre" => $r->nombres . ' ' . $r->apellidoPaterno . ' (' . $r->mail . ')',
		];

        return response()->json($integrante);
    }

    public function delete(DeleteIntegrante $id, $idEquipo, $idIntegrante)
	{
        $integrante = Integrante::findOrFail($idIntegrante);
		$integrante->delete();

		return response()->json('OK', 200);
	}
}
