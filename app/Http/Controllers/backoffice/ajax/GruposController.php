<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\GrupoRolPersona;
use App\Http\Controllers\BaseController;
use App\Http\Resources\MiembroResource;
use Illuminate\Http\Request;
use App\Grupo;
use App\Persona;
use Illuminate\Support\Facades\DB;

class GruposController extends BaseController
{
    public function index($idGrupo, Request $request)
    {
        $perPage = $request->per_page ?? 10;
        $array = [
            'nombre' => [
                'grupo' => 'Grupo.nombre',
                'persona' => 'Persona.nombres',
            ],
            'rol' => [
                'persona' => 'Grupo_Persona.rol'
            ]
        ];

        $grupos = $this->queryGrupos($request, $idGrupo, $array)->get();
        $personas = $this->queryPersonas($request, $idGrupo, $array)->get();
        $miembros = $grupos->merge($personas);
        $collection = [];
        foreach ($miembros as $i => $item) {
            $collection[] = new MiembroResource($item);
        }
        return $this->paginate($collection, $perPage);
    }

    public function store(Request $request)
    {
        $validate = $request->validate([
            'nombre'        => 'required|max:254',
            'idPadre'       => 'required',
            'idActividad'   => 'required',
        ]);
        $grupo = Grupo::create($request->all());
        return new MiembroResource($grupo);
    }

    public function incluirInscripto($idGrupo, Request $request)
    {
        $validate = $request->validate([
            'idPersona'     => 'required|numeric',
            'idGrupo'       => 'required|numeric',
            'idActividad'   => 'required|numeric',
        ]);

        $persona = GrupoRolPersona::where('idPersona', '=', $request->idPersona)
            ->where('idActividad', '=', $request->idActividad)
            ->first();
        if (!$persona) {
            $id = DB::table('Grupo_Persona')->insertGetId($request->all());
            $result = array_merge($request->all(), ['idPersona' => $id, 'tipo' => 'persona']);
            return json_encode($result);
        }
        $grupo = Grupo::find($persona->idPadre);
        return response($grupo, 428);
    }

    private function queryPersonas(Request $request, $idGrupo, $array)
    {
        list($sort, $orderBy) = explode('|', $request->sort);

        $personas = Persona::join('Grupo_Persona', 'Persona.idPersona', '=', 'Grupo_Persona.idPersona')
            ->where('Grupo_Persona.idActividad', '=', $request->idActividad)
            ->where('Grupo_Persona.idGrupo', '=', $idGrupo);

        if ($request->has('filter')) {
            $filter = $request->filter;
            $personas->where(function ($query) use ($filter) {
                $query->orWhere('Persona.nombres', 'like', '%' . $filter . '%');
                $query->orWhere('Persona.apellidoPaterno', 'like', '%' . $filter . '%');
                $query->orWhere('Grupo_Persona.rol', 'like', '%' . $filter . '%');
            });
        }

        if (!empty($array[$sort]['persona'])) {
            $personas->orderBy($array[$sort]['persona'], $orderBy);
        }

        return $personas;
    }

    private function queryGrupos(Request $request, $idGrupo, $array)
    {
        list($sort, $orderBy) = explode('|', $request->sort);
        $grupos = Grupo::where('idPadre', '=', $idGrupo)
            ->where('idActividad', '=', $request->idActividad);

        if ($request->has('filter')) {
            $filter = $request->filter;
            $grupos->where(function ($query) use ($filter) {
                $query->orWhere('nombre', 'like', '%' . $filter . '%');
            });
        }
        if (!empty($array[$sort]['grupo'])) {
            $grupos->orderBy($array[$sort]['grupo'], $orderBy);
        }

        return $grupos;
    }
}
