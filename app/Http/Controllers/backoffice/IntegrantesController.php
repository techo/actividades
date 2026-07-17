<?php

namespace App\Http\Controllers\backoffice;

use App\Equipo;
use App\Http\Controllers\Controller;
use App\Services\Listados\IntegrantesCatalogo;

use Illuminate\Http\Request;

class IntegrantesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexAll(Request $request, $idEquipo)
    {
        $equipo = Equipo::findOrFail($idEquipo);
        // Primer render: fijas + columnas por defecto. La configuración real del
        // usuario la carga el selector de columnas vía GET ajax/listados/.../config.
        $fields = json_encode((new IntegrantesCatalogo)->defaultFields($idEquipo));
        $sortOrder = json_encode(config('datatables.integrantes.sortOrder'));
        return view('backoffice.equipos.personas.indexAll', compact('fields', 'sortOrder', 'idEquipo', 'equipo'));
    }

    public function indexActive(Request $request, $idEquipo)
    {
        $equipo = Equipo::findOrFail($idEquipo);
        $fields = json_encode((new IntegrantesCatalogo)->defaultFields($idEquipo));
        $sortOrder = json_encode(config('datatables.integrantes.sortOrder'));
        return view('backoffice.equipos.personas.indexActive', compact('fields', 'sortOrder', 'idEquipo', 'equipo'));
    }

}
