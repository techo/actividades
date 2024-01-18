<?php

namespace App\Http\Controllers\backoffice;

use App\Provincia;
use App\CategoriaActividad;
use App\Http\Controllers\Controller;
use App\Http\Requests\Provincia\CrearProvincia;
use App\Pais;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Contracts\Provider;

class ProvinciasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $datatableConfig = config('datatables.provincias');
        $fields = json_encode($datatableConfig['fields']);
        $sortOrder = json_encode($datatableConfig['sortOrder']);
        return view('backoffice.configuracion.provincias.index', compact('fields', 'sortOrder'));
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
            'backoffice.configuracion.provincias.create',
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
    public function store(CrearProvincia $request)
    {
        $provincia = new Provincia();
        $provincia->provincia = $request->nombre; 
        $provincia->id_pais =  auth()->user()->idPaisPermitido; 
        $provincia->save(); 

        return response()->json($provincia->fresh());

    }

    // /**
    //  * Update the specified resource in storage.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @param  int  $id
    //  * @return \Illuminate\Http\Response
    //  */
    public function update(CrearProvincia $request, $id)
    {
        $provincia = Provincia::findOrFail($id);
        // $validado = $validado = $request->validated();
        $provincia->provincia = $request->nombre;
        $provincia->save();

        return response()->json($provincia);
    }
    
    // /**
    //  * Display the specified resource.
    //  *
    //  * @param  int  $id
    //  * @return \Illuminate\Http\Response
    //  */
    public function show($id)
    {
        $provincia = Provincia::findOrFail($id);
        $edicion = false;

        return view(
            'backoffice.configuracion.provincias.show',
            compact(
                'provincia',
                'edicion'
            )
        );

    }

    // /**
    //  * Show the form for editing the specified resource.
    //  *
    //  * @param  int  $id
    //  * @return \Illuminate\Http\Response
    //  */
    // public function edit($id)
    // {
    //     //
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  *
    //  * @param  int  $id
    //  * @return \Illuminate\Http\Response
    //  */
    // public function destroy($id)
    // {
    //     $equipo = Equipo::findOrFail($id);


    //     try {
    //         $equipo->delete();

    //     } catch (\Exception $exception) {
    //         return response($exception->getMessage(), 500);
    //     }
    //     Session::flash('mensaje', 'El equipo se eliminó correctamente');
    //     return redirect()->to('/admin/equipos');
    // }

}
