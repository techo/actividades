<?php

namespace App\Http\Controllers\ajax;

use App\CategoriaActividad;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoriasController extends Controller
{
    public function show(Request $request)
    {
        return CategoriaActividad::find($request->id)->tipos()->get();
    }
}
