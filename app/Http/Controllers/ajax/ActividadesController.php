<?php

namespace App\Http\Controllers\ajax;

use App\Http\Controllers\BaseController;
use App\Http\Resources\ActividadCollection;
use App\Http\Resources\ActividadResource;
use App\Search\ActividadesSearch;
use App\Search\LocalidadesSearch;
use App\Search\TiposActividadesSearch;
use Illuminate\Http\Request;
use App\Actividad;

use Illuminate\Support\Facades\DB;

class actividadesController extends BaseController
{
    /**
     * Devuelve un JSON de actividades
     *
     * @param int $items Cantidad de elementos en cada página
     * @return ActividadCollection
     */
    public function index(Request $request, $items=6)
    {
        $actividades = $this->filtrar($request);

        if ($actividades->count() > 0) {
            foreach ($actividades as $actividad) {
                $actividad->descripcion = clean_string($actividad->descripcion);
                $resourceCollection[] = new ActividadResource($actividad);
            }
            return $this->paginate($resourceCollection, $items, $request->query());
        }
        return $actividades;
    }

    /**
     * Devuelve el JSON de una actividad
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $actividad = Actividad::find($id);
        $actividad->descripcion = clean_string($actividad->descripcion);
        return new ActividadResource($actividad);
    }

    private function filtrar(Request $request)
    {
        return ActividadesSearch::apply($request);
    }

    /**
     * Devuelve un JSON de las provincias y localidades que matchean con los filtros actuales
     *
     * @param Request $request
     * @return mixed
     */
    public function filtrarProvinciasYLocalidades(Request $request)
    {

        $provincias = LocalidadesSearch::apply($request);

        $listProvincias = [];

        for ($i = 0; $i < count($provincias); $i++) {
            $idProvincia = (int)$provincias[$i]->id_provincia;
            $listProvincias[$idProvincia]['id_provincia'] = $idProvincia;
            $listProvincias[$idProvincia]['provincia'] = $provincias[$i]->Provincia;
            if($provincias[$i]->id_localidad) {
                $listProvincias[$idProvincia]['localidades'][] =
                    array('id_localidad' => $provincias[$i]->id_localidad,
                        'localidad' => $provincias[$i]->Localidad);
            }
            else {
                $listProvincias[$idProvincia]['localidades'] = [];
            }
        }
        return $listProvincias;
    }

    /**
     * Devuelve un JSON de los tipos de actividad que matchean con los filtros actuales
     *
     * @param Request $request
     * @return mixed
     */
    public function filtrarTiposDeActividades(Request $request)
    {

        $tipos = TiposActividadesSearch::apply($request);

        $listTipos = [];

        for ($i = 0; $i < count($tipos); $i++) {
            $idTipo = $tipos[$i]->idTipo;
            $listTipos[$i]['idTipo'] = $idTipo;
            $listTipos[$i]['nombre'] = $tipos[$i]->nombre;
        }
        return $listTipos;
    }


}