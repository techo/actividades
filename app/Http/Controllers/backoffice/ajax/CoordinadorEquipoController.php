<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\CoordinadorEquipo;
use App\Equipo;
use App\Http\Controllers\Controller;
use App\Integrante;
use Illuminate\Http\Request;

use App\Http\Requests\Equipo\CrearIntegrante;
use App\Http\Requests\Equipo\DeleteIntegrante;
use App\Persona;

class CoordinadorEquipoController extends Controller
{
    public function index($idEquipo)
    {
        $coordinadores = CoordinadorEquipo::where('idEquipo', $idEquipo)->with('persona')->get();
        return response()->json($coordinadores);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store( $idEquipo, $idPersona)
    {

        $equipo = Equipo::findOrFail($idEquipo);
        $persona = Persona::findOrFail($idPersona);

        $coordinador = new CoordinadorEquipo();
        $coordinador->idPersona = $persona->idPersona;
        $coordinador->idEquipo = $equipo->idEquipo;

        $coordinador->save();

        return response()->json($coordinador->fresh());

    }

    public function delete($idEquipo, $idCoordinador)
	{
        $coordinador = CoordinadorEquipo::findOrFail($idCoordinador);
		$coordinador->delete();

		return response()->json('OK', 200);
	}
}
