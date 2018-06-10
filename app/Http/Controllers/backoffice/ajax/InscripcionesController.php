<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Actividad;
use App\Exports\InscripcionesExport;
use App\GrupoRolPersona;
use App\Inscripcion;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\DB;

class InscripcionesController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id, Request $request)
    {
        $filtros = array_merge($request->all(), ['idActividad' => $id]);
        if($request->has('filter')){
            $filtros['HotFilter'] = $request->filter;
            unset($filtros['filter']);
        }
        if($request->has('condiciones'))
        {
            foreach ($request->condiciones as $condicion)
            {
                $condicion = json_decode($condicion, true);
                $filtros[$condicion['campo']] = [
                    'condicion' => $condicion['condicion'],
                    'valor' => $condicion['valor']
                ];
            }
            unset($filtros['condiciones']);
        }
        $export = new InscripcionesExport($filtros);
        $collection = $export->collection();
        $result = $this->paginate($collection, 10);
        return $result;

    }

    public function update(Request $request, $id, $inscripcion)
    {
        $inscripcion = Inscripcion::findOrFail($inscripcion);
        $inscripcion->presente = $request->presente;
        $inscripcion->pago = $request->pago;
        if ($request->estado !== null) {
            $inscripcion->estado = $request->estado;
        }

        if ($inscripcion->save()) {
            return response('Ok');
        }

        return response('Ocurrió un error al actualizar el estado', 500);
    }

    public function asignarRol(Request $request)
    {
        $idActividad = $request->actividad;
        foreach ($request->inscripciones as $idInscripcion)
        {
            $persona = Inscripcion::findOrFail($idInscripcion)->persona;
            if($grupoRol = $persona->grupoAsignadoEnActividad($idActividad))
            {
                $grupoRol->rol = $request->rol;
                $grupoRol->save();
            } else {
                //Nuevo
                $grupoRol = new GrupoRolPersona();
                $grupoRol->idPersona = $persona->idPersona;
                $grupoRol->idActividad = $idActividad;
                $grupoRol->idGrupo = Actividad::find($idActividad)->grupos()->raiz()->idGrupo;
                $grupoRol->rol = $request->rol;
                $grupoRol->save();
            }
        }
        return response()
            ->json("Rol " . $request->rol . " configurado a " . $request->inscripciones->count() . " voluntarios correctamente.", 200);
    }

    public function asignarGrupo(Request $request)
    {
        $idActividad = $request->actividad;
        foreach ($request->inscripciones as $idInscripcion)
        {
            $persona = Inscripcion::findOrFail($idInscripcion)->persona;
            if($grupoRol = $persona->grupoAsignadoEnActividad($idActividad))
            {
                $grupoRol->idGrupo = $request->grupo['id'];
                $grupoRol->save();
            } else {
                //Nuevo
                $grupoRol = new GrupoRolPersona();
                $grupoRol->idPersona = $persona->idPersona;
                $grupoRol->idActividad = $idActividad;
                $grupoRol->idGrupo = $request->grupo['id'];
                $grupoRol->rol = "";
                $grupoRol->save();
            }
        }
        return response()
            ->json("Grupo " . $request->grupo['nombre']. " configurado a " . count($request->inscripciones) . " voluntarios correctamente.", 200);
    }

    public function cambiarEstado(Request $request, $id)
    {
        foreach ($request->inscripciones as $idInscripcion)
        {
            $inscripcion = Inscripcion::findOrFail($idInscripcion);
            $inscripcion->estado = $request->estado;
            $inscripcion->save();
        }
        return response()
            ->json("Estado actualizado a " . $request->estado . " en " . count($request->inscripciones) . " voluntarios correctamente.", 200);
    }

    public function cambiarAsistencia(Request $request, $id)
    {
        foreach ($request->inscripciones as $idInscripcion)
        {
            $inscripcion = Inscripcion::findOrFail($idInscripcion);
            $inscripcion->presente = $request->asistencia;
            $inscripcion->save();
        }

        $msgAsistencia = $request->asistencia === 1 ? "Presente" : "Ausente";
        return response()
            ->json("Asistencia actualizada a " . $msgAsistencia . " en " . count($request->inscripciones) . " voluntarios correctamente.", 200);
    }
}
