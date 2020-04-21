<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\Controller;
use App\Oficina;
use App\Search\OficinasSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;


class OficinasController extends Controller
{
    public function getOficinas()
    {
        return Oficina::with('pais')->get();
    }
    public function getOficinasPais($idpais)
    {
        return Oficina::where('id_pais', $idpais)->get();
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

    public function store(Request $request) {
        $oficina = $request->validate([
            'nombre' => 'required',
            'id_pais' => 'required',
        ]);

        Oficina::create($oficina);

        return response()->json($oficina);
    }

    public function update(Request $request) {
        $validados = $request->validate([
            'id' => 'required',
            'nombre' => 'required',
            'id_pais' => 'required',
        ]);

        $oficina = Oficina::find($validados['id']);
        $oficina->update($validados);


        return response()->json($validados);
    }

    public function delete(Oficina $id)
    {
        if ($id->delete()){
            Session::flash('mensaje', 'Oficina eliminada correctamente');
        } else {
            Session::flash('mensaje', 'Ocurrio un error al querer eliminar a la Oficina');
        }
        return redirect()->to('/admin/configuracion/oficinas');
    }


    public function get($id)
    {
        $oficina = Oficina::find($id);
        return response()->json($oficina);
    }
}
