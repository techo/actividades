<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comunidad\DeleteComunidad;
use App\Http\Requests\Comunidad\ReferenteComunidad as RequestReferenteComunidad;
use App\Http\Resources\ReferenteComunidadResource;
use App\Search\ReferenteComunidadSearch;
use Illuminate\Http\Request;

use App\ReferenteComunidad;

class ReferenteComunidadController extends Controller
{
    public function index(Request $request, $idComunidad)
    {
        $filtros = [];
        if($request->has('red')){
            $filtros['nombre'] = $request->red;
        }
        
        $filtros['idComunidad'] = $idComunidad;
        
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

        $result = ReferenteComunidadSearch::apply($filtros, $sort, $per_page);
        $referenteComunidad = ReferenteComunidadResource::collection($result); // Yo se que es horrible pero no funciona sin esto
        return response()->json($result);
    }

    public function store(RequestReferenteComunidad $request, $idComunidad)
    {
        $referenteComunidad = new ReferenteComunidad();
        $validado = $request->validated();
        $referenteComunidad->fill($validado);

        $referenteComunidad->save();

        return response()->json($referenteComunidad->fresh());

    }

    public function update(RequestReferenteComunidad $request, $idComunidad, $idReferenteComunidad)
    {
        $referenteComunidad = ReferenteComunidad::findOrFail($idReferenteComunidad);
        $validado = $request->validated();
        $referenteComunidad->fill($validado);
        $referenteComunidad->save();

        return response()->json($referenteComunidad);
    }

    public function get($idComunidad, $idReferenteComunidad)
    {
        $referenteComunidad = ReferenteComunidad::findOrFail($idReferenteComunidad);
        return response()->json($referenteComunidad);
    }

    public function delete(DeleteComunidad $id, $idComunidad, $idReferenteComunidad)
	{
        $referenteComunidad = ReferenteComunidad::findOrFail($idReferenteComunidad);
		$referenteComunidad->delete();

		return response()->json('OK', 200);
	}
}
