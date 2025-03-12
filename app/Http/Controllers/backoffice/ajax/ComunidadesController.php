<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Comunidad;
use App\Http\Controllers\Controller;
use App\Http\Requests\Comunidad\CrearComunidad;
use App\Http\Resources\ComunidadesResource;
use App\Oficina;
use App\Search\ComunidadesSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ComunidadesController extends Controller
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

        $result = ComunidadesSearch::apply($filtros, $sort, $per_page);
        $comunidades = ComunidadesResource::collection($result); // Yo se que es horrible pero no funciona sin esto
        return response()->json($result);
    }

    public function store(CrearComunidad $request)
    {
        $comunidad = new Comunidad();
        $validado = $request->validated();
        $oficina = Oficina::find($validado['idOficina']);
        $comunidad->fill($validado);
        $comunidad->idPais = $oficina->id_pais;

        $comunidad->save();

        return response()->json($comunidad->fresh());

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CrearComunidad $request, $id)
    {
        $equipo = Comunidad::findOrFail($id);
        $validado = $validado = $request->validated();
        $oficina = Oficina::findOrFail($validado['idOficina']);
        $equipo->fill($validado);
        $equipo->idPais = $oficina->id_pais;
        $equipo->save();

        return response()->json($equipo);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $comunidad = Comunidad::findOrFail($id);


        try {
            $comunidad->delete();

        } catch (\Exception $exception) {
            return response($exception->getMessage(), 500);
        }
        return redirect()->to('/admin/comunidades');
    }

    
}
