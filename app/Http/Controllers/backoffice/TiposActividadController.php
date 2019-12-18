<?php

namespace App\Http\Controllers\backoffice;

use App\Oficina;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class TiposActividadController extends Controller
{
	public function index()
    {
        $datatableConfig = config('datatables.tiposActividad');
        $fields = json_encode($datatableConfig['fields']);
        $sortOrder = json_encode($datatableConfig['sortOrder']);
        return view('backoffice.tiposActividad.index', compact('fields', 'sortOrder'));
    }

    public function create()
    {
        $edicion = true;
        return view('backoffice.oficinas.create', compact('edicion'));
    }

    public function show(Request $request, $id)
    {
        $oficina = Oficina::find($id);
        $edicion = false;
        return view('backoffice.oficinas.show', compact('edicion', 'oficina'));
    }

    

}
