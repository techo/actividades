<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\Controller;
use App\Oficina;
use App\Search\OficinasSearch;
use Illuminate\Http\Request;

class OficinasController extends Controller
{
    public function getOficinas()
    {
        return Oficina::all();
    }

    public function index(Request $request)
    {
        $filtros = [];
        if($request->has('oficina')){
            $filtros['oficina'] = $request->oficina;
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

        $result = OficinasSearch::apply($filtros, $sort, $per_page);
        return response()->json($result);
    }
}
