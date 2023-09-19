<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Actividad;
use App\Http\Controllers\Controller;
use App\Search\LocalidadesDataSearch;
use App\Localidad;
use Illuminate\Http\Request;

use App\Http\Requests\Provincia\CrearLocalidad;
use App\Http\Requests\Provincia\DeleteLocalidad;

class LocalidadesController extends Controller
{
    public function index(Request $request, $idProvincia)
    {
        $filtros = [];
        if($request->has('filter')){
            $filtros['localidad'] = $request->filter;
        }

        $filtros['id_provincia'] = $idProvincia;
        
        if($request->filled('sort')) {
            if(strpos($request->sort, "|"))
                $sort = join(" ",explode("|", $request->sort));
            else
                $sort = $request->sort;
        }

        $per_page = 25;
        if($request->filled('per_page')) {
            $per_page = $request->per_page;
        }

        $result = LocalidadesDataSearch::apply($filtros, $sort, $per_page);
        return response()->json($result);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CrearLocalidad $request, $idProvincia)
    {
        $localidad = new Localidad();
        
        $localidad->id_provincia = $request->idProvincia;
        $localidad->localidad = $request->nombre;
        $localidad->save();

        return response()->json($localidad->fresh());
    }

    public function update(CrearLocalidad $request, $idProvincia, $idLocalidad)
    {
        $localidad = Localidad::findOrFail($request->id);

        $localidad->localidad = $request->nombre;

        $localidad->save();

        return response()->json($localidad);
    }

    // /**
    //  * Get a resource in storage.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return \Illuminate\Http\Response
    //  */
    public function get(Request $request, $idProvincia, $idLocalidad)
    {
        $localidad = Localidad::findOrFail($idLocalidad);
        $r = $localidad->provincia;
		$localidad->nombre = $localidad->localidad;
        $localidad->provinciaData = [
			"idProvincia" => $r->id,
			"nombre" => $r->nombre
		];

        return response()->json($localidad);
    }

    public function delete(DeleteLocalidad $id, $idProvincia, $idLocalidad)
	{
        $actividades = Actividad::where('idLocalidad', $idLocalidad);
        if($actividades->count()){
            $mensaje = "Existen ".$actividades->count()." asociadas a esta división, primero debe editar esas actividades";

            return response()->json($mensaje, 200);
        }
        $localidad = Localidad::findOrFail($idLocalidad);
		$localidad->delete();

		return response()->json('ok', 200);
	}
}
