<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\Controller;
use App\Http\Resources\RolResource;
use App\Http\Resources\SuscriptosResource;
use App\Search\SuscriptosSearch;
use App\Suscribe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuscriptosController extends Controller
{
    public function index(Request $request)
    {
        $filtros = [];
        if($request->has('usuario')){
            $filtros['usuario'] = $request->usuario;
        }
        
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

        $result = SuscriptosSearch::apply($filtros, $sort, $per_page);
        $suscriptos = SuscriptosResource::collection($result); // Yo se que es horrible pero no funciona sin esto
        return response()->json($result);
    }

    public function getSuscriptosSemana($id)
    {
        $rol = Suscribe::find($id)->roles()->first();
        return new RolResource($rol);
    }

    
}
