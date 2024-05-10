<?php

namespace App\Http\Controllers\backoffice;

use App\Equipo;
use App\CoordinadorEquipo;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class CoordinadorEquipoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $idEquipo)
    {
        $id = $idEquipo;
        $equipo = Equipo::where('idEquipo', $idEquipo)->first();
        return view('backoffice.equipos.coordinadores.index', compact('id', 'equipo'));
    }

}
