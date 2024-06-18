<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\Controller;
use App\Http\Resources\InstitucionEducativaResource;
use App\Search\InstitucionEducativaSearch;
use Illuminate\Http\Request;

class InstitucionEducativaController extends Controller
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

        $result = InstitucionEducativaSearch::apply($filtros, $sort, $per_page);
        $provincias = InstitucionEducativaResource::collection($result); // Yo se que es horrible pero no funciona sin esto
        return response()->json($result);
    }

    
}
