<?php

namespace App\Http\Controllers\backoffice;

use App\InstitucionEducativa;
use App\Http\Controllers\Controller;
use App\Http\Requests\CrearInstitucionEducativa;
use Illuminate\Http\Request;

class InstitucionEducativaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $datatableConfig = config('datatables.institucionEducativa');
        $fields = json_encode($datatableConfig['fields']);
        $sortOrder = json_encode($datatableConfig['sortOrder']);
        return view('backoffice.configuracion.institucionEducativa.index', compact('fields', 'sortOrder'));
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
            'backoffice.configuracion.institucionEducativa.create',
            compact(
                'edicion'
            )
        );
    }

    // /**
    //  * Store a newly created resource in storage.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return \Illuminate\Http\Response
    //  */
    public function store(CrearInstitucionEducativa $request)
    {
        $institucionEducativa = new InstitucionEducativa();
        $institucionEducativa->nombre = $request->nombre; 
        $institucionEducativa->idPais =  auth()->user()->idPaisPermitido; 
        $institucionEducativa->save(); 

        return response()->json($institucionEducativa->fresh());

    }

    // /**
    //  * Update the specified resource in storage.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @param  int  $id
    //  * @return \Illuminate\Http\Response
    //  */
    public function update(CrearInstitucionEducativa $request, $id)
    {
        $InstitucionEducativa = InstitucionEducativa::findOrFail($id);
        if(auth()->user()->idPaisPermitido == $InstitucionEducativa->idPais){
            $InstitucionEducativa->nombre = $request->nombre;
            $InstitucionEducativa->save(); 
        }
        
        return response()->json($InstitucionEducativa);
    }
    
    // // /**
    // //  * Display the specified resource.
    // //  *
    // //  * @param  int  $id
    // //  * @return \Illuminate\Http\Response
    // //  */
    public function show($id)
    {
        $institucionEducativa = InstitucionEducativa::findOrFail($id);
        $edicion = false;

        return view(
            'backoffice.configuracion.institucionEducativa.show',
            compact(
                'institucionEducativa',
                'edicion'
            )
        );

    }

}
