<?php

namespace App\Http\Controllers\backoffice\ajax;


use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;

class ActividadesController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sortDefault = 'nombreActividad|asc';
        $request->sort = $request->sort ?? $sortDefault;
        $sort = explode('|',$request->sort);
        list($sortField, $sortOrder) = $sort;

        $result = DB::table('Actividad')
            ->leftJoin('atl_oficinas', 'Actividad.idOficina', '=', 'atl_oficinas.id')
            ->join('Tipo', 'Tipo.idTipo', '=', 'Actividad.idTipo')
            ->join('atl_CategoriaActividad', 'Tipo.idCategoria', '=', 'atl_CategoriaActividad.id')
            ->select(
                [
                    'idActividad as id',
                    'nombreActividad',
                    'fechaInicio',
                    'fechaFin',
                    'estadoConstruccion',
                    'atl_oficinas.id',
                    'atl_oficinas.nombre AS oficina',
                    'Tipo.nombre AS tipoActividad',
                    'atl_CategoriaActividad.nombre as nombreCategoria',
                    'atl_CategoriaActividad.id as idCategoria',
                ]
            )
        ->orderBy($sortField, $sortOrder);

        if ($request->has('filter')){
            $result->orWhere(function($result) use ($request) {
                $result->orWhere('nombreActividad', 'like', '%'. $request->filter . '%');
                $result->orWhere('estadoConstruccion', 'like', '%'. $request->filter . '%');
                $result->orWhere('Tipo.nombre', 'like', '%'. $request->filter . '%');
                $result->orWhere('atl_oficinas.nombre', 'like', '%' . $request->filter . '%');
            });
        }

        $result = $result->get();
        $result = $this->paginate($result,10);
        return $result;
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
        //
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
