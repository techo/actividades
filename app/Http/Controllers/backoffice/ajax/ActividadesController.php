<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Actividad;
use App\Exports\ActividadesExport;
use App\Http\Controllers\BaseController;
use App\PuntoEncuentro;
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
        $export = new ActividadesExport($request->filter, $request->sort);
        $collection = $export->collection();
        $result = $this->paginate($collection, 10);
        return $result;
    }

    public function grupos($id)
    {
        $actividad = Actividad::findorFail($id);
        return $actividad->grupos;
    }

    public function getPuntos($id)
    {
       $query = (new PuntoEncuentro)->newQuery();
       return $query->join('Persona', 'PuntoEncuentro.idPersona', '=', 'Persona.idPersona')
           ->join('atl_pais', 'PuntoEncuentro.idPais', '=', 'atl_pais.id')
           ->join('atl_provincias', 'PuntoEncuentro.idProvincia', '=', 'atl_provincias.id')
           ->leftJoin('atl_localidades', 'PuntoEncuentro.idLocalidad', '=', 'atl_localidades.id')
           ->where('idActividad', $id)
           ->select(
               'idPuntoEncuentro as id',
               'punto',
               'atl_pais.nombre as pais',
               'atl_provincias.provincia as provincia',
               'atl_localidades.localidad as localidad',
               'Persona.nombres',
               'Persona.apellidoPaterno')
           ->get();
    }

}
