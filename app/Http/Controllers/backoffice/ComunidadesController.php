<?php

namespace App\Http\Controllers\backoffice;

use App\Comunidad;
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
