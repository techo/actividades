<?php

namespace App\Http\Controllers\ajax;

use App\Http\Resources\ActividadCollection;
use App\Http\Resources\ActividadResource;
use App\Localidad;
use App\Provincia;
use App\PuntoEncuentro;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Actividad;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use League\Flysystem\Adapter\Local;

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
        return ActividadResource::collection(Actividad::orderBy('fechaInicio','desc')->paginate($items));
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

    public function filtrar(Request $request)
    {
        $query = DB::table('Actividad')
            ->join('PuntoEncuentro', 'Actividad.idActividad', '=', 'PuntoEncuentro.idActividad')
            ->join('atl_pais', 'PuntoEncuentro.idPais', '=', 'atl_pais.id')
            ->join('atl_provincias', 'PuntoEncuentro.idProvincia', '=', 'atl_provincias.id')
            ->join('atl_localidades', 'PuntoEncuentro.idLocalidad', '=', 'atl_localidades.id')
            ->selectRaw('distinct Actividad.*')
            ->orderBy('fechaInicio', 'desc');

        if ($request->has('provincia')) {
            $query->where('provincia', $request->provincia);
        }

        if ($request->has('localidades')) {
            $query->whereIn('localidad', $request->localidades);
        }

        if ($request->has('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $actividades = $query->get();

        $actividades = Actividad::hydrate($actividades->toArray());

        foreach ($actividades as $actividad) {
            $resourceCollection[] = new ActividadResource($actividad);
        }

        return $this->paginate($resourceCollection, 6, $request->query());
    }

    function paginate($items, $perPage, $parameters = null)
    {
        if(is_array($items)){
            $items = collect($items);
        }

        return new LengthAwarePaginator(
            $items->forPage(Paginator::resolveCurrentPage() , $perPage),
            $items->count(),
            $perPage,
            Paginator::resolveCurrentPage(),
            ['path' => Paginator::resolveCurrentPath(), 'query' => $parameters]
        );
    }
}
