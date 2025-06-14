<?php

namespace App\Http\Controllers\backoffice;

use App\Comunidad;
use App\ComunidadFichaInicial;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ComunidadesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $datatableConfig = config('datatables.comunidades');
        $fields = json_encode($datatableConfig['fields']);
        $sortOrder = json_encode($datatableConfig['sortOrder']);
        return view('backoffice.comunidades.index', compact('fields', 'sortOrder'));
    }

    public function getActividades(Request $request, $idComunidad)
    {
        $comunidad = Comunidad::findOrFail($idComunidad);
        $datatableConfig = config('datatables.actividades');
        $fields = json_encode($datatableConfig['fields']);
        $sortOrder = json_encode($datatableConfig['sortOrder']);
        return view('backoffice.comunidades.actividades.index', compact('fields', 'sortOrder', 'idComunidad', 'comunidad'));
    }

    public function getEquipos(Request $request, $idComunidad)
    {
        $comunidad = Comunidad::findOrFail($idComunidad);
        $datatableConfig = config('datatables.equipos');
        $fields = json_encode($datatableConfig['fields']);
        $sortOrder = json_encode($datatableConfig['sortOrder']);
        return view('backoffice.comunidades.equipos.index', compact('fields', 'sortOrder', 'idComunidad', 'comunidad'));
    }

    public function showFicha(Request $request, $idComunidad)
    {
        $edicion = true;
        $comunidad = Comunidad::findOrFail($idComunidad);
        $ficha = ComunidadFichaInicial::where('idComunidad', $idComunidad)->first();
        return view('backoffice.comunidades.ficha.show', compact('comunidad', 'ficha', 'edicion'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $edicion = true;

        return view(
            'backoffice.comunidades.create',
            compact(
                'edicion'
            )
        );
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $comunidad = Comunidad::findOrFail($id);
        $edicion = false;

        return view(
            'backoffice.comunidades.show',
            compact(
                'comunidad',
                'edicion'
            )
        );

    }
    
}
