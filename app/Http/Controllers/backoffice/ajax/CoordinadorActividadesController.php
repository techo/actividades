<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CoordinadorActividadesController extends BaseController
{
    public function index(Request $request)
    {
        $sortDefault = 'nombreActividad|asc';
        $request->sort = $request->sort ?? $sortDefault;
        $sort = explode('|',$request->sort);
        list($sortField, $sortOrder) = $sort;

        $result = DB::table('Actividad')
            ->leftJoin('atl_oficinas', 'Actividad.idOficina', '=', 'atl_oficinas.id')
            ->join('Tipo', 'Tipo.idTipo', '=', 'Actividad.idTipo')
            ->join('atl_CategoriaActividad', 'Tipo.idCategoria', '=', 'atl_CategoriaActividad.id')
            ->select(
                [
                    'Actividad.idActividad AS id',
                    'nombreActividad',
                    'fechaInicio',
                    'fechaFin',
                    'estadoConstruccion',
                    'atl_oficinas.nombre AS oficina',
                    'Tipo.nombre AS tipoActividad',
                    'atl_CategoriaActividad.nombre as nombreCategoria',
                    'atl_CategoriaActividad.id as idCategoria',
                ]
            )
            ->orderBy($sortField, $sortOrder);

        if (!Auth::user()->hasRole('admin')) {
            $result->where('idCoordinador', Auth::user()->idPersona);
        }

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
