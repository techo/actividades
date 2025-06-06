<?php

namespace App\Http\Controllers\backoffice;

use App\Comunidad;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class CoordinadorComunidadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $idComunidad)
    {
        $comunidad = Comunidad::where('idComunidad', $idComunidad)->with('coordinadores')->first();
        return view('backoffice.comunidades.coordinadores.index', compact('idComunidad', 'comunidad'));
    }

}
