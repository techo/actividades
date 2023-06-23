<?php

namespace App\Http\Controllers\backoffice;

use App\Equipo;
use App\Actividad;
use App\CategoriaActividad;
use App\Grupo;
use App\GrupoRolPersona;
use App\Http\Controllers\Controller;
use App\Http\Requests\Equipo\CrearEquipo;
use App\Jobs\EnviarMailsCancelacionActividad;
use App\Pais;
use App\Persona;
use App\PuntoEncuentro;
use App\Coordinador;
use App\Oficina;
use App\Rules\FechaFinActividad;
use App\UnidadOrganizacional;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class EquiposController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $datatableConfig = config('datatables.equipos');
        $fields = json_encode($datatableConfig['fields']);
        $sortOrder = json_encode($datatableConfig['sortOrder']);
        return view('backoffice.equipos.index', compact('fields', 'sortOrder'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $edicion = true;
        $paises = Pais::has("provincias")->get();
        $columns = Schema::getColumnListing('Actividad');
        $arrayColumnas = array_fill_keys($columns, null);


        $actividad = json_encode($arrayColumnas);
        $categorias = CategoriaActividad::with('tipos')->get();
        $tipos = $categorias->first()->tipos;
        $categorias = json_encode($categorias);
        $edicion = true;

        return view(
            'backoffice.equipos.create',
            compact(
                'actividad',
                'paises',
                'edicion',
                'tipos',
                'categorias',
                'edicion'
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
                'edicion'
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
