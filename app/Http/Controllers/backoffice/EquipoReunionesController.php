<?php

namespace App\Http\Controllers\backoffice;

use App\Equipo;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class EquipoReunionesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $idEquipo)
    {
        $equipo = Equipo::findOrFail($idEquipo);
        $datatableConfig = config('datatables.reuniones_equipo');
        $fields = json_encode($datatableConfig['fields']);
        $sortOrder = json_encode($datatableConfig['sortOrder']);
        return view('backoffice.equipos.seguimiento.index', compact('fields', 'sortOrder', 'idEquipo', 'equipo'));
    }

}
