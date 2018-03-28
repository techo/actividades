<?php

namespace App\Http\Controllers\ajax;

use App\CategoriaActividad;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class CategoriasController extends Controller
{
    public function index()
    {
        return CategoriaActividad::all();
    }

    public function show(Request $request)
    {
        $categoria = CategoriaActividad::find($request->id);
        $categoria->load('tipos');
        return $categoria;
    }

    public function tipos(Request $request)
    {
        $categoria = CategoriaActividad::find($request->id);
        return $categoria->tipos;
    }
}
