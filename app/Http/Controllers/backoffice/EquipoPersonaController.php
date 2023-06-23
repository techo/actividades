<?php

namespace App\Http\Controllers\backoffice;

use App\Equipo;
use App\Http\Controllers\Controller;
use App\Http\Requests\Equipo\CrearEquipo;
use App\Persona;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

class EquipoPersonaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $idEquipo)
    {
        $datatableConfig = config('datatables.equipoPersonas');
        $fields = json_encode($datatableConfig['fields']);
        $sortOrder = json_encode($datatableConfig['sortOrder']);
        return view('backoffice.equipos.personas.index', compact('fields', 'sortOrder', 'idEquipo'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($idEquipo)
    {
        $edicion = true;

        return view(
            'backoffice.equipos.personas.create',
            compact(
                'edicion',
                'idEquipo',
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CrearEquipo $request)
    {
        $equipo = new Equipo();
        $validado = $request->validated();
        $oficina = Oficina::find($validado['idOficina']);
        $equipo->fill($validado);
        $equipo->idPais = $oficina->id_pais;
        $equipo->activo = true;

        $equipo->save();

        return response()->json($equipo->fresh());

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CrearEquipo $request, $id)
    {
        $equipo = Equipo::findOrFail($id);
        $validado = $validado = $request->validated();
        $oficina = Oficina::findOrFail($validado['idOficina']);
        $equipo->fill($validado);
        $equipo->idPais = $oficina->id_pais;
        $equipo->save();

        return response()->json($equipo);
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $equipo = Equipo::findOrFail($id);
        $edicion = false;

        return view(
            'backoffice.equipos.show',
            compact(
                'equipo',
                'edicion',
            )
        );

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $equipo = Equipo::findOrFail($id);


        try {
            $equipo->delete();

        } catch (\Exception $exception) {
            return response($exception->getMessage(), 500);
        }
        Session::flash('mensaje', 'El equipo se eliminó correctamente');
        return redirect()->to('/admin/equipos');
    }

}
