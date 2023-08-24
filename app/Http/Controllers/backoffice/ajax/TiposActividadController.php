<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\Controller;
use App\Tipo;
use App\Search\TiposActividadSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;


class TiposActividadController extends Controller
{
    public function index(Request $request)
    {
        $filtros = [];
        if($request->has('tiposActividad')){
            $filtros['tipo'] = $request->tiposActividad;
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

        $result = TiposActividadSearch::apply($filtros, $sort, $per_page);
        return response()->json($result);
    }

    public function store(Request $request) {
        $tipoActividad = $request->validate([
            'nombre' => 'required',
            'idCategoria' => 'required',
        ]);

        Tipo::create($tipoActividad);

        return response()->json($tipoActividad);
    }

    public function update(Request $request) {
        $validados = $request->validate([
            'idTipo' => 'required',
            'nombre' => 'required',
            'idCategoria' => 'required',
            'imagen' => 'nullable|file|image|dimensions:max_width=380,max_height=248',
        ]);

        $tipoActividad = Tipo::find($validados['idTipo']);
        

        $imagen = $request->file('imagen');
        if($imagen){
            $path = $request->file('imagen')->store('public/tipos');
            $tipoActividad->imagen = str_replace('public', 'storage', '/'.$path);
        }
        $tipoActividad->nombre = $validados['nombre'];
        $tipoActividad->idCategoria = $validados['idCategoria'];
        
        $tipoActividad->save();

        return response()->json($validados);
    }

    public function delete(Tipo $id)
    {
        if ($id->delete()){
            Session::flash('mensaje', 'Tipo de Actividad eliminada correctamente');
        } else {
            Session::flash('mensaje', 'Ocurrio un error al querer eliminar a el Tipo de Actividad');
        }
        return redirect()->to('/admin/configuracion/tipos-actividad');
    }


    

    public function get($id)
    {
        $tipoActividad = Tipo::find($id);
        return response()->json($tipoActividad);
    }
}
