<?php

namespace App\Http\Controllers\ajax;

use App\Http\Resources\ActividadCollection;
use App\Http\Resources\ActividadResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Actividad;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class actividadesController extends Controller
{
    /**
     * Devuelve un JSON de actividades
     *
     * @param int $items Cantidad de elementos en cada página
     * @return ActividadCollection
     */
    public function index(Request $request, $items=6)
    {
        //return ActividadResource::collection(Actividad::orderBy('fechaInicio','desc')->paginate($items));
        return $this->filtrar($request, $items);
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

    private function filtrar(Request $request, $items)
    {
        $query = DB::table('Actividad')
            ->join('PuntoEncuentro', 'Actividad.idActividad', '=', 'PuntoEncuentro.idActividad')
            ->join('Tipo', 'Actividad.idTipo', '=', 'Tipo.idTipo')
            ->join('atl_pais', 'PuntoEncuentro.idPais', '=', 'atl_pais.id')
            ->join('atl_provincias', 'PuntoEncuentro.idProvincia', '=', 'atl_provincias.id')
            ->join('atl_localidades', 'PuntoEncuentro.idLocalidad', '=', 'atl_localidades.id')
            ->selectRaw('distinct Actividad.*')
            ->orderBy('fechaInicio', 'desc');

        if ($request->has('categoria') && !is_null($request->categoria)) {
            $query->where('Tipo.idCategoria', $request->categoria);
        }

        if ($request->has('provincia') && !is_null($request->provincia)) {
            $query->where('atl_provincias.id', $request->provincia);
        }

        if ($request->has('localidades') && !is_null($request->localidades)) {
            $query->whereIn('atl_localidades.id', array($request->localidades));
        }

        if ($request->has('tipo') && !is_null($request->tipo)) {
            $query->where('Tipo.idTipo', $request->tipo);
        }

        $actividades = $query->get();

        $actividades = Actividad::hydrate($actividades->toArray());

        if ($actividades->count() > 0) {
            foreach ($actividades as $actividad) {
                $resourceCollection[] = new ActividadResource($actividad);
            }
            return $this->paginate($resourceCollection, $items, $request->query());
        }
        return $actividades;

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

    /**
     * @param Request $request
     * @param $idCategoria
     * @return mixed
     */
    public function filtrarProvinciasYLocalidades(Request $request)
    {

        $default = 1; //Actividades en Asentamientos
        $categoria = $request->categoria ?? $default;

        $provincias = DB::table('Actividad')
            ->join('Tipo', 'Actividad.idTipo', '=', 'Tipo.idTipo')
            ->join('PuntoEncuentro', 'Actividad.idActividad', '=', 'PuntoEncuentro.idActividad')
            ->join('atl_provincias', 'PuntoEncuentro.idProvincia', '=', 'atl_provincias.id')
            ->join('atl_localidades', 'PuntoEncuentro.idLocalidad', '=', 'atl_localidades.id')
            ->where('Tipo.idCategoria', $categoria)
            ->orderBy('atl_provincias.id', 'desc')
            ->orderBy('atl_localidades.id', 'desc')
            ->selectRaw('distinct atl_provincias.id id_provincia, atl_provincias.Provincia, 
                                         atl_localidades.id id_localidad, atl_localidades.Localidad');

        if ($request->has('tipo') && !is_null($request->tipo)) {
            $provincias->where('Tipo.idTipo', $request->tipo);
        }

        $provincias = $provincias->get();
        $listProvincias = [];

        for ($i = 0; $i < count($provincias); $i++) {
            $idProvincia = $provincias[$i]->id_provincia;
            $listProvincias[$idProvincia]['id_provincia'] = $idProvincia;
            $listProvincias[$idProvincia]['provincia'] = $provincias[$idProvincia]->Provincia;
            $listProvincias[$idProvincia]['localidades'][] =
                array('id_localidad' => $provincias[$i]->id_localidad,
                    'localidad' => $provincias[$i]->Localidad);
        }
        return $listProvincias;
    }
}
