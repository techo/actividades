<?php

namespace App\Http\Controllers\backoffice;

use App\Provincia;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class LocalidadesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $idProvincia)
    {
        $provincia = Provincia::findOrFail($idProvincia);
        $datatableConfig = config('datatables.locaclidades');
        $fields = json_encode($datatableConfig['fields']);
        $sortOrder = json_encode($datatableConfig['sortOrder']);
        return view('backoffice.configuracion.provincias.localidades.index', compact('fields', 'sortOrder', 'idProvincia', 'provincia'));
    }

}
