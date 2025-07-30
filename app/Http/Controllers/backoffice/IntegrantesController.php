<?php

namespace App\Http\Controllers\backoffice;

use App\Equipo;
use App\Http\Controllers\Controller;

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
        $datatableConfig = config('datatables.integrantes');
        $fields = json_encode($datatableConfig['fields']);
        $sortOrder = json_encode($datatableConfig['sortOrder']);
        return view('backoffice.equipos.personas.indexAll', compact('fields', 'sortOrder', 'idEquipo', 'equipo'));
    }

    public function indexActive(Request $request, $idEquipo)
    {
        $equipo = Equipo::findOrFail($idEquipo);
        $datatableConfig = config('datatables.integrantes');
        $fields = json_encode($datatableConfig['fields']);
        $sortOrder = json_encode($datatableConfig['sortOrder']);
        return view('backoffice.equipos.personas.indexActive', compact('fields', 'sortOrder', 'idEquipo', 'equipo'));
    }

}
