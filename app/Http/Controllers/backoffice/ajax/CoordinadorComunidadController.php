<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\CoordinadorComunidad;
use App\Comunidad;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateComunidad;
use App\Persona;

class CoordinadorComunidadController extends Controller
{
    public function index($idComunidad)
    {
        $coordinadores = CoordinadorComunidad::where('idComunidad', $idComunidad)->with('persona')->get();
        return response()->json($coordinadores);
    }

    public function store(UpdateComunidad $request, $idComunidad, $idPersona)
    {

        $comunidad = Comunidad::findOrFail($idComunidad);
        $persona = Persona::findOrFail($idPersona);

        $coordinador = new CoordinadorComunidad();
        $coordinador->idPersona = $persona->idPersona;
        $coordinador->idComunidad = $comunidad->idComunidad;

        $coordinador->save();

        return response()->json($coordinador->fresh());

    }

    public function delete(UpdateComunidad $request, $idComunidad, $idCoordinador)
	{
        $coordinador = CoordinadorComunidad::findOrFail($idCoordinador);
		$coordinador->delete();

		return response()->json('OK', 200);
	}
}
