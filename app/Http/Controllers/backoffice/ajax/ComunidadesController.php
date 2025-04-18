<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Comunidad;
use App\Exports\ActividadesExport;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Comunidad\CrearComunidad;
use App\Http\Resources\ComunidadesResource;
use App\Http\Resources\EquiposResource;
use App\Oficina;
use App\Search\ComunidadesSearch;
use App\Search\EquiposSearch;
use Illuminate\Http\Request;

class ComunidadesController extends BaseController
{
    public function index(Request $request)
    {
        $filtros = [];
        if($request->has('comunidad')){
            $filtros['comunidad'] = $request->comunidad;
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

    public function getActividades(Request $request, $idComunidad)
    {
        $per_page = 25;
        if($request->filled('per_page')) {
            $per_page = $request->per_page;
        }

        $export = new ActividadesExport($request->filter, $request->sort, $idComunidad);
        $collection = $export->collection();
        $result = $this->paginate($collection, $per_page);
        return $result;
    }

    public function getEquipos(Request $request, $idComunidad)
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

        $result = EquiposSearch::apply($filtros, $sort, $per_page, $idComunidad);
        $equipos = EquiposResource::collection($result); // Yo se que es horrible pero no funciona sin esto
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
