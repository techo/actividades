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
                'persona' => 'Inscripcion.rol'
            ]
        ];

        $grupos = $this->queryGrupos($request, $idGrupo, $array)->get();
        $personas = $this->queryPersonas($request, $idGrupo, $array)->get();
        $miembros = $grupos->merge($personas);
        $collection = [];
        foreach ($miembros as $i => $item) {
            $collection[] = new MiembroResource($item);
        }
        $result = $this->paginate($collection, $perPage);
        $flattenCollection = $result->getCollection()->flatten();
        $result->setCollection($flattenCollection);
        return $result;
    }

    public function store(Request $request)
    {
        $validate = $request->validate([
            'nombre'        => 'required|max:254',
            'idPadre'       => 'required',
            'linkEvaluacion'=> 'url|nullable',
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

        $membresia = GrupoRolPersona::where('idPersona', '=', $request->idPersona)
            ->where('idActividad', '=', $request->idActividad)
            ->first();

        if ($membresia) {
            //si está en el grupo raíz
            if($membresia->grupo->idPadre == 0) {
                $membresia->idGrupo = $request->idGrupo;
                $membresia->save();
                return json_encode($membresia);
            }
            else {
                $grupo = $membresia->grupo;
                return response($grupo, 428);
            }
        }

        //no estaba inscripto, error
        return response($grupo, 500);
    }

    private function queryPersonas(Request $request, $idGrupo, $array)
    {
        list($sort, $orderBy) = explode('|', $request->sort);

        $personas = Persona::join('Grupo_Persona', 'Persona.idPersona', '=', 'Grupo_Persona.idPersona')
            ->join('Inscripcion', function ($join) {
                $join->on('Inscripcion.idPersona', '=', 'Grupo_Persona.idPersona');
                $join->on('Inscripcion.idActividad', '=', 'Grupo_Persona.idActividad');
            })
            ->where('Grupo_Persona.idActividad', '=', $request->idActividad)
            ->where('Grupo_Persona.idGrupo', '=', $idGrupo);

        if ($request->has('filter')) {
            $filter = $request->filter;
            $personas->where(function ($query) use ($filter) {
                $query->orWhere('Persona.nombres', 'like', '%' . $filter . '%');
                $query->orWhere('Persona.apellidoPaterno', 'like', '%' . $filter . '%');
                $query->orWhere('Inscripcion.rol', 'like', '%' . $filter . '%');
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
