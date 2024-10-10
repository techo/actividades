<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Actividad;
use App\Coordinador;
use App\Exports\ActividadesExport;
use App\Exports\JornadasExport;
use App\Http\Controllers\BaseController;
use App\Http\Requests\CrearCoordinador;
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
        $per_page = 25;
        if($request->filled('per_page')) {
            $per_page = $request->per_page;
        }

        $export = new ActividadesExport($request->filter, $request->sort);
        $collection = $export->collection();
        $result = $this->paginate($collection, $per_page);
        return $result;
    }

    public function grupos($id)
    {
        $actividad = Actividad::findorFail($id);
        return $actividad->grupos;
    }

    public function jornadas(Request $request, $id)
    {
        $per_page = 25;
        if($request->filled('per_page')) {
            $per_page = $request->per_page;
        }

        $export = new JornadasExport($request->filter, $request->sort, $id);
        $collection = $export->collection();
        $result = $this->paginate($collection, $per_page);
        return $result;
    }



    public function storeImagenTarjeta(Request $request, $id){
        
        $validate = $request->validate([
            'imagen_tarjeta' => 'nullable|file|image',
        ]);

        $imagen = $validate['imagen_tarjeta'];
        
        $actividad = Actividad::findorFail($id);

        if($imagen){
            $path = $request->file('imagen_tarjeta')->store('public/actividades');
            $actividad->imagen_tarjeta = str_replace('public', 'storage', '/'.$path);
            $actividad->save();
        }
        return $actividad->imagen_tarjeta;
    }

    public function puntos(Actividad $id)
    {
       $query = (new PuntoEncuentro)->newQuery();
       return $query->join('Persona', 'PuntoEncuentro.idPersona', '=', 'Persona.idPersona')
           ->join('atl_pais', 'PuntoEncuentro.idPais', '=', 'atl_pais.id')
           ->join('atl_provincias', 'PuntoEncuentro.idProvincia', '=', 'atl_provincias.id')
           ->leftJoin('atl_localidades', 'PuntoEncuentro.idLocalidad', '=', 'atl_localidades.id')
           ->where('idActividad', $id->idActividad)
           ->select(
               'idPuntoEncuentro as id',
               'punto',
               'atl_pais.nombre as pais',
               'atl_provincias.provincia as provincia',
               'atl_localidades.localidad as localidad',
               'Persona.nombres',
               'Persona.apellidoPaterno',
               'horario',
               'estado')
           ->get();
    }

    public function activaWhatsappAccesos(CrearCoordinador $request, Actividad $actividad, Coordinador $coordinador)
    {    
        $coordinador->activaWhatsapp = ! $coordinador->activaWhatsapp;
        $coordinador->save();
        return response()->json($coordinador);
    }
}
