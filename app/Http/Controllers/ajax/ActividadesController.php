<?php

namespace App\Http\Controllers\ajax;

use App\Http\Resources\ActividadCollection;
use App\Http\Resources\ActividadResource;
use App\PuntoEncuentro;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Actividad;

class actividadesController extends Controller
{
    /**
     * Devuelve un JSON de actividades
     *
     * @param int $items Cantidad de elementos en cada página
     * @return ActividadCollection
     */
    public function index($items=6)
    {
        return new ActividadCollection(Actividad::paginate($items));
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
        return new ActividadResource(Actividad::find($id));
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
