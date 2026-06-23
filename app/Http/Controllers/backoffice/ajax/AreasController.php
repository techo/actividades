<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Area;
use App\Http\Controllers\Controller;

class AreasController extends Controller
{
    /** Catálogo de áreas activas para los selects del backoffice. */
    public function index()
    {
        return Area::where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'clave', 'nombre']);
    }
}
