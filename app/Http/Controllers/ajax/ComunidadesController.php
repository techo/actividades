<?php

namespace App\Http\Controllers\ajax;

use App\Comunidad;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ComunidadesController extends Controller
{
    public function index()
    {
        return Comunidad::all();
    }

    public function indexOficina($idOficina)
    {
        return Comunidad::where('idOficina', $idOficina)->get();
    }

    public function show(Request $request)
    {
        $comunidad = Comunidad::find($request->id);
        return $comunidad;
    }
}
