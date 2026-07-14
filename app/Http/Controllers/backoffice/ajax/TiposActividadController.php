<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\Controller;
use App\Tipo;
use App\Search\TiposActividadSearch;
use App\Services\ImageUploadService;
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
            'nombre_pt' => 'nullable',
            'nombre_en' => 'nullable',
            'idCategoria' => 'required',
            'imagen' => 'nullable|file|image|dimensions:max_width=380,max_height=248,min_width=380,min_height=248',
            'activo' => 'required|boolean',
            'tipo_indicador' => 'required'
        ]);

        Tipo::create($tipoActividad);

        return response()->json($tipoActividad);
    }

    public function update(Request $request) {
        $validados = $request->validate([
            'idTipo' => 'required',
            'nombre' => 'required',
            'nombre_pt' => 'nullable',
            'nombre_en' => 'nullable',
            'idCategoria' => 'required',
            'imagen' => 'nullable|file|image|dimensions:max_width=380,max_height=248,min_width=380,min_height=248',
            'activo' => 'required|boolean',
            'tipo_indicador' => 'required'
        ]);

        $tipoActividad = Tipo::find($validados['idTipo']);
        

        $imagen = $request->file('imagen');
        if($imagen){
            $path = ImageUploadService::store($request->file('imagen'), 'public/tipos');
            $tipoActividad->imagen = str_replace('public', 'storage', '/'.$path);
        }
        $tipoActividad->nombre = $validados['nombre'];
        $tipoActividad->nombre_pt = $validados['nombre_pt'];
        $tipoActividad->nombre_en = $validados['nombre_en'];
        $tipoActividad->idCategoria = $validados['idCategoria'];
        $tipoActividad->tipo_indicador = $validados['tipo_indicador'];
        $tipoActividad->activo = $validados['activo'];
        
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
