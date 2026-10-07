<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Actividad;
use App\GrupoRolPersona;
use App\Inscripcion;
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
        if (!empty($validate['linkEvaluacion'])) {
            $validate['linkEvaluacion'] = rtrim(strstr($validate['linkEvaluacion'], '/viewform', true), '/') . '/';
        }
        $grupo = Grupo::create($validate);
        return new MiembroResource($grupo);
    }

    public function incluirInscripto($idGrupo, Request $request)
    {
        $validate = $request->validate([
            'idPersona'     => 'required|numeric',
            'idGrupo'       => 'required|numeric',
            'idActividad'   => 'required|numeric',
        ]);

        $actividad = Actividad::findOrFail($request->idActividad);
        $this->authorize('editar', $actividad);

        $grupoDestino = Grupo::where('idGrupo', $idGrupo)
            ->where('idActividad', $actividad->idActividad)
            ->firstOrFail();

        $membresia = GrupoRolPersona::where('idPersona', '=', $request->idPersona)
            ->where('idActividad', '=', $actividad->idActividad)
            ->first();

        if ($membresia) {
            // Si está en la raíz (o su grupo ya no existe), se mueve al destino.
            if (!$membresia->grupo || $membresia->grupo->idPadre == 0) {
                $membresia->idGrupo = $grupoDestino->idGrupo;
                $membresia->save();
                return json_encode($membresia);
            }

            return response($membresia->grupo, 428);
        }

        // Inscripto sin fila en Grupo_Persona (legacy o membresía perdida): se crea en el destino.
        $inscripto = Inscripcion::where('idPersona', $request->idPersona)
            ->where('idActividad', $actividad->idActividad)
            ->exists();

        if ($inscripto) {
            $membresia = GrupoRolPersona::create([
                'idPersona'   => (int) $request->idPersona,
                'idActividad' => $actividad->idActividad,
                'idGrupo'     => $grupoDestino->idGrupo,
                'rol'         => '',
            ]);
            return json_encode($membresia);
        }

        return response()->json([
            'message' => 'La persona no está inscripta en esta actividad. Inscribila primero desde la pestaña Inscripciones.',
        ], 422);
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
