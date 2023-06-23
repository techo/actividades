<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\Controller;
use App\Http\Resources\EquipoPersonasResource;
use App\Search\EquipoPersonasSearch;
use App\EquipoPersonas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EquipoPersonasController extends Controller
{
    public function index(Request $request)
    {
        $filtros = [];
        if($request->has('nombre')){
            $filtros['nombre'] = $request->nombre;
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

        $result = EquipoPersonasSearch::apply($filtros, $sort, $per_page);
        $equipos = EquipoPersonasResource::collection($result); // Yo se que es horrible pero no funciona sin esto
        return response()->json($result);
    }

    
}
