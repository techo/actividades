<?php

namespace App\Http\Controllers\backoffice;

use App\Actividad;
use App\Pais;
use App\Persona;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ActividadesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $datatableConfig = config('datatables.actividades');
        $fields = json_encode($datatableConfig['fields']);
        $sortOrder = json_encode($datatableConfig['sortOrder']);



        return view('backoffice.actividades.index', compact('fields', 'sortOrder'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $edicion = false;
        $paises = Pais::all();
        $coordinadores = Persona::take(10)->get();
        $actividad = Actividad::with(
            'tipo.categoria',
            'unidadOrganizacional',
            'modificadoPor',
            'puntosEncuentro',
            'pais',
            'provincia',
            'localidad'
        )
            ->where('idActividad', $id)
            ->first();
        try {
            $provincias = $actividad->pais->provincias;
            $localidades = $actividad->provincia->localidades;

        } catch (\Exception $e) {
            $provincias = null;
            $localidades = null;
        }
        return view(
            'backoffice.actividades.show',
            compact(
                'actividad',
                'paises',
                'coordinadores',
                'provincias',
                'localidades',
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
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
        //
    }



}
