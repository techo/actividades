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
use Illuminate\Support\Facades\Storage;

class IntegrantesController extends Controller
{
    public function index(Request $request, $idEquipo)
    {
        $filtros = [];
        if($request->has('integrante')){
            $filtros['integrante'] = $request->integrante;
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

    public function uploadArchivos(Request $request, $idEquipo, $idIntegrante)
    {
        $this->validate($request, array(
            'archivo_carta_compromiso' => 'nullable',
            'archivo_plan_de_trabajo' => 'nullable',
        ));

        $integrante = Integrante::findOrFail($idIntegrante);

        if ($request->file('archivo_carta_compromiso')){
            $archivo = $request->file('archivo_carta_compromiso');
            $path = $archivo->store('public/integrante');
            $oldPath = str_replace('storage', 'public', $integrante->archivo_carta_compromiso);
            if(Storage::exists($oldPath))
                Storage::delete($oldPath);

            $integrante->archivo_carta_compromiso = str_replace('public', 'storage', $path);
            $integrante->save();
        }

        if ($request->file('archivo_plan_de_trabajo')){
            $archivo = $request->file('archivo_plan_de_trabajo');
            $path = $archivo->store('public/integrante');
            $oldPath = str_replace('storage', 'public', $integrante->archivo_plan_de_trabajo);
            if(Storage::exists($oldPath))
                Storage::delete($oldPath);

            $integrante->archivo_plan_de_trabajo = str_replace('public', 'storage', $path);
            $integrante->save();
        }
        return response()->json($integrante);
  }
}
