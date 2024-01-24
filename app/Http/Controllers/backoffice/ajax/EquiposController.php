<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\Controller;
use App\Http\Resources\RolResource;
use App\Http\Resources\EquiposResource;
use App\Search\EquiposSearch;
use App\Equipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EquiposController extends Controller
{
    public function index(Request $request)
    {
        $filtros = [];
        if($request->has('equipo')){
            $filtros['equipo'] = $request->equipo;
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

        $result = EquiposSearch::apply($filtros, $sort, $per_page);
        $equipos = EquiposResource::collection($result); // Yo se que es horrible pero no funciona sin esto
        return response()->json($result);
    }

    
}
