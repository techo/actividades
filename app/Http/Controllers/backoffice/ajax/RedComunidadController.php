<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comunidad\DeleteComunidad;
use App\Http\Requests\Comunidad\RedComunidad as RequestRedComunidad;
use App\Http\Resources\RedComunidadResource;
use App\Search\RedComunidadSearch;
use Illuminate\Http\Request;

use App\RedComunidad;

class RedComunidadController extends Controller
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

        $result = RedComunidadSearch::apply($filtros, $sort, $per_page);
        $redComunidad = RedComunidadResource::collection($result); // Yo se que es horrible pero no funciona sin esto
        return response()->json($result);
    }

    public function store(RequestRedComunidad $request, $idComunidad)
    {
        $redComunidad = new RedComunidad();
        $validado = $request->validated();
        $redComunidad->fill($validado);

        $redComunidad->save();

        return response()->json($redComunidad->fresh());

    }

    public function update(RequestRedComunidad $request, $idComunidad, $idRedComunidad)
    {
        $redComunidad = RedComunidad::findOrFail($idRedComunidad);
        $validado = $request->validated();
        $redComunidad->fill($validado);
        $redComunidad->save();

        return response()->json($redComunidad);
    }

    public function get($idComunidad, $idRedComunidad)
    {
        $redComunidad = RedComunidad::findOrFail($idRedComunidad);
        return response()->json($redComunidad);
    }

    public function delete(DeleteComunidad $id, $idComunidad, $idRedComunidad)
	{
        $redComunidad = RedComunidad::findOrFail($idRedComunidad);
		$redComunidad->delete();

		return response()->json('OK', 200);
	}
}
