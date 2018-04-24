<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CoordinadorActividadesController extends BaseController
{
    public function index(Request $request){
        $sortDefault = 'nombreActividad|asc';
        $request->sort = $request->sort ?? $sortDefault;
        $sort = explode('|',$request->sort);
        list($sortField, $sortOrder) = $sort;

        $result = DB::table('Actividad')
            ->join('UnidadOrganizacional', 'Actividad.idUnidadOrganizacional', '=', 'UnidadOrganizacional.idUnidadOrganizacional')
            ->join('Tipo', 'Tipo.idTipo', '=', 'Actividad.idTipo')
            ->join('atl_CategoriaActividad', 'Tipo.idCategoria', '=', 'atl_CategoriaActividad.id')
            ->select(
                [
                    'idActividad as id',
                    'nombreActividad',
                    'fechaInicio',
                    'fechaFin',
                    'estadoConstruccion',
                    'UnidadOrganizacional.idUnidadOrganizacional',
                    'UnidadOrganizacional.nombre AS nombreUnidad',
                    'Tipo.nombre AS tipoActividad',
                    'atl_CategoriaActividad.nombre as nombreCategoria',
                    'atl_CategoriaActividad.id as idCategoria',
                ]
            )
            ->orderBy($sortField, $sortOrder)
            ->where('idCoordinador', Auth::user()->idPersona); //Falta mergear branch de Johan para hacer esto

        if ($request->has('filter')){
            $result->orWhere(function($result) use ($request) {
                $result->orWhere('nombreActividad', 'like', '%'. $request->filter . '%');
                $result->orWhere('estadoConstruccion', 'like', '%'. $request->filter . '%');
                $result->orWhere('Tipo.nombre', 'like', '%'. $request->filter . '%');
                $result->orWhere('UnidadOrganizacional.nombre', 'like', '%'. $request->filter . '%');
            });
        }

        $result = $result->get();

        $result = $this->paginate($result,10);
        return $result;
    }
}
