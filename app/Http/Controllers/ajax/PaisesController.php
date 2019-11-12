<?php

namespace App\Http\Controllers\ajax;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Pais;
use App\Provincia;

class PaisesController extends Controller
{
    public function index(Request $request)
    {
        return Pais::orderBy('nombre')->get();
    }

    public function paises(Request $request)
    {
        return Pais::has('actividades')->orderBy('nombre')->get();
    }

    public function provincias($id_pais) {
    	return Pais::find($id_pais)->provincias;
    }

    public function localidades($id_pais, $id_provincia) {
    	return Provincia::find($id_provincia)->localidades;
    }

    public function paisesConCodigo() {
        return Pais::whereNotNull('codigo')->orderBy('nombre')->get();
    }

}
