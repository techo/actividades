<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\Controller;
use App\RolIntegrante;

class RolesIntegranteController extends Controller
{
    /** Catálogo de roles de integrante activos para los selects del backoffice. */
    public function index()
    {
        return RolIntegrante::where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'clave', 'nombre']);
    }
}
