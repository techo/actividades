<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Actividad;
use App\Exports\InscripcionesExport;
use App\GrupoRolPersona;
use App\Inscripcion;
use App\Persona;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Mail\MailConfimacionInscripcion;
use App\Mail\ActualizacionActividad;
use Illuminate\Support\Facades\Mail;

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

        if($request->has('presente')){
            $inscripcion->presente = $request->presente;
        }

        if($request->has('pago')){
            $inscripcion->pago = $request->pago;
        }

        if (!empty($request->estado)){
            $inscripcion->estado = $request->estado;
        }
        if ($inscripcion->save()) {
            return response()->json('Ok', 200);
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
            ->json("Rol " . $request->rol . " configurado a " . count($request->inscripciones) . " voluntarios correctamente.", 200);
    }

    public function asignarGrupo(Request $request)
    {
        $datos = $request->all();
        $idActividad = $request->actividad;
        foreach ($request->inscripciones as $idInscripcion)
        {
            $persona = Inscripcion::findOrFail($idInscripcion)->persona;
            if($grupoRol = $persona->grupoAsignadoEnActividad($idActividad))
            {
                $grupoRol->idGrupo = $datos['grupo']['idGrupo'];
                $grupoRol->save();
            } else {
                //Nuevo
                $grupoRol = new GrupoRolPersona();
                $grupoRol->idPersona = $persona->idPersona;
                $grupoRol->idActividad = $idActividad;
                $grupoRol->idGrupo = $datos['grupo']['idGrupo'];
                $grupoRol->rol = "";
                $grupoRol->save();
            }
        }
        return response()
            ->json("Grupo " . $request->grupo['nombre']. " configurado a " . count($request->inscripciones) . " voluntarios correctamente.", 200);
    }

    public function asignarPunto($idActividad, Request $request)
    {
        foreach ($request->inscripciones as $idInscripcion) {
            $inscripcion = Inscripcion::findOrFail($idInscripcion);
            $inscripcion->idPuntoEncuentro = $request->punto;
            $inscripcion->save();
            Mail::to($inscripcion->persona->mail)->send(new ActualizacionActividad($inscripcion));
        }
        return response()
            ->json("Punto de encuentro actualizado en " . count($request->inscripciones) . " voluntarios correctamente.", 200);
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

    public function getInscriptos($id, Request $request)
    {
        if($request->has('inscriptos')){
            $filtros['inscriptos'] = $request->inscriptos;
            $filtros['idActividad'] = $id;
        }

        $export = new InscripcionesExport($filtros);
        $collection = $export->collection();
//        $result = $collection->only(['idPersona', 'nombres', 'apellidoPaterno']);
        return $collection;

    }

    public function store($id, Request $request)
    {
        $user = Persona::findOrFail($request->idPersona);
        $yaInscripto = Inscripcion::where('idPersona', '=', $request->idPersona)
            ->where('idActividad', '=', $id)
            ->first();
        if ($yaInscripto) {
            return response('Voluntario ya inscripto', 428);
        }
        $inscripcion = $this->inscribir($request);
        $grupo = $this->incluirEnGrupo($request);
        if ($inscripcion &&  $grupo) {
            Mail::to($user->mail)->send(new MailConfimacionInscripcion($inscripcion));
            return response('ok');
        }

        return response('Error al guardar la Inscripción', 500);
    }

    private function incluirEnGrupo(Request $request)
    {
        $arr = [
            'idPersona' => (int)$request->idPersona,
            'idGrupo' => (int)$request->idGrupo,
            'idActividad' => (int)$request->idActividad,
            'rol' => $request->rol
        ];

        return GrupoRolPersona::create($arr);
    }

    private function inscribir(Request $request)
    {
        $data = [
            'idActividad'       => (int)$request->idActividad,
            'idPersona'         => (int)$request->idPersona,
            'fechaInscripcion'  => Carbon::now(),
            'idPersonaModificacion' => auth()->user()->idPersona,
            'idPuntoEncuentro'  => $request->idPuntoEncuentro,
            'estado'            => 'Sin Contactar',
            'evaluacion'        => 0,
            'acompanante'       => ''
        ];

        return Inscripcion::create($data);
    }
}
