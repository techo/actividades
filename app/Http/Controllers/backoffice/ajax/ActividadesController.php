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
            ->join('UnidadOrganizacional', 'Actividad.idUnidadOrganizacional', '=', 'UnidadOrganizacional.idUnidadOrganizacional')
            ->join('Tipo', 'Tipo.idTipo', '=', 'Actividad.idTipo')
            ->select(
                [
                    'idActividad',
                    'nombreActividad',
                    'fechaInicio',
                    'fechaFin',
                    'estadoConstruccion',
                    'UnidadOrganizacional.idUnidadOrganizacional',
                    'UnidadOrganizacional.nombre AS nombreUnidad',
                    'Tipo.nombre AS tipoActividad'
                ]
            )
        ->orderBy($sortField, $sortOrder)->take(21)->get();

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
