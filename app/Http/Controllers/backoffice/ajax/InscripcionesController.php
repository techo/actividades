<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Actividad;
use App\Exports\InscripcionesExport;
use App\Grupo;
use App\GrupoRolPersona;
use App\Http\Controllers\BaseController;
use App\Http\Requests\CrearInscripcion;
use App\Inscripcion;
use App\Log;
use App\Mail\ActualizacionActividad;
use App\Mail\MailInscripcionConfirmada;
use App\Mail\MailInscripcionEsperarConfirmacion;
use App\Mail\MailInscripcionFaltaPago;
use App\Persona;
use App\PuntoEncuentro;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB as DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Rap2hpoutre\FastExcel\FastExcel;

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

        //hack para solucionar problema con vuetable con checkboxes
        // https://github.com/ratiw/vuetable-2/issues/422
        $flattenCollection = $result->getCollection()->flatten();
        $result->setCollection($flattenCollection);

        return $result;

    }

    public function update(CrearInscripcion $request, $id, $inscripcion)
    {
        $inscripcion = Inscripcion::findOrFail($inscripcion);

        if($request->has('presente')){
            $inscripcion->presente = $request->presente;
        }

        if($request->has('confirma')){

            if($request->confirma == true) {
                if($inscripcion->actividad->confirmacion == 1 && $inscripcion->actividad->pago == 0) 
                    $this->intentaEnviar(new MailInscripcionConfirmada($inscripcion), $inscripcion->persona);

                if($inscripcion->actividad->confirmacion == 1 && $inscripcion->actividad->pago == 1)
                    $this->intentaEnviar(new MailInscripcionFaltaPago($inscripcion), $inscripcion->persona);
            }

            $inscripcion->confirma = $request->confirma;
        }

        if($request->has('pago')){
            if($inscripcion->actividad->pago == 1 && $request->pago == 1) {
                $this->intentaEnviar(new MailInscripcionConfirmada($inscripcion), $inscripcion->persona);
            }

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

    public function destroy(CrearInscripcion $request, $id, $inscripcion)
    {
        $inscripcion = Inscripcion::findOrFail($inscripcion);

        if ($inscripcion){
            $inscripcion->delete();
            return response()->json('Ok', 200);
        }

        return response('Ocurrió un error al eliminar la inscripción', 500);
    }

    public function desinscribir(CrearInscripcion $request)
    {
        foreach ($request->inscripciones as $idInscripcion)
        {
            Inscripcion::findOrFail($idInscripcion)->delete();
        }

        return response()
            ->json(count($request->inscripciones) . " inscripciones eliminadas.", 200);
    }

    public function asignarRol(CrearInscripcion $request)
    {
        $idActividad = $request->actividad;
        foreach ($request->inscripciones as $idInscripcion)
        {
            $inscripcion = Inscripcion::findOrFail($idInscripcion);
            $inscripcion->rol = $request->rol;
            $inscripcion->save();
        }
        return response()
            ->json("Rol " . $request->rol . " configurado a " . count($request->inscripciones) . " voluntarios correctamente.", 200);
    }

    public function asignarGrupo(CrearInscripcion $request)
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

    public function asignarPunto($idActividad, CrearInscripcion $request)
    {
        foreach ($request->inscripciones as $idInscripcion) {
            $inscripcion = Inscripcion::findOrFail($idInscripcion);
            $inscripcion->idPuntoEncuentro = $request->punto;
            $inscripcion->save();
            $this->intentaEnviar(new ActualizacionActividad($inscripcion), $inscripcion->persona);
        }
        return response()
            ->json("Punto de encuentro actualizado en " . count($request->inscripciones) . " voluntarios correctamente.", 200);
    }

    public function cambiarConfirmacion(CrearInscripcion $request, $id)
    {
        foreach ($request->inscripciones as $idInscripcion)
        {
            $inscripcion = Inscripcion::findOrFail($idInscripcion);
            $inscripcion->confirma = $request->confirmacion;
            $inscripcion->save();
        }

        $msg = $request->confirma === 1 ? "Confirmado" : "Sin Confirmar";
        return response()
            ->json("Asistencia actualizada a " . $msg . " en " . count($request->inscripciones) . " voluntarios correctamente.", 200);
    }

    public function cambiarPago(CrearInscripcion $request, $id)
    {
        foreach ($request->inscripciones as $idInscripcion)
        {
            $inscripcion = Inscripcion::findOrFail($idInscripcion);
            $inscripcion->pago = $request->pago;
            $inscripcion->save();
        }

        $msg = $request->pago === 1 ? "Pagado" : "Sin Pagar";
        return response()
            ->json("Asistencia actualizada a " . $msg . " en " . count($request->inscripciones) . " voluntarios correctamente.", 200);
    }

    public function cambiarAsistencia(CrearInscripcion $request, $id)
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

    public function getInscriptos($id, CrearInscripcion $request)
    {
        if($request->has('inscriptos')){
            $filtros['inscriptos'] = $request->inscriptos;
            $filtros['idActividad'] = $id;
            $export = new InscripcionesExport($filtros);
            $collection = $export->collection();
            return $collection;
        }

        return response('La petición debe tener el parámetro "inscriptos"', 500);
    }

    public function store($id, CrearInscripcion $request)
    {
        $idPuntoEncuentro = $request->idPuntoEncuentro;

        $request->validate([
            'idPuntoEncuentro' => 'required',
            'idPersona' => [
                'required', 
                Rule::unique('Inscripcion')
                    ->where(function ($query) use ($id, $idPuntoEncuentro) {
                        return $query->where('idActividad', $id)->whereNull('deleted_at');
                })],
            'notificar' => 'sometimes|nullable',
        ]);

        $request['idActividad'] = $id;

        $inscripto = Inscripcion::withTrashed()
            ->where('idPersona', '=', $request->idPersona)
            ->where('idActividad', '=', $id)
            ->first();

        if ($inscripto) {
            if ($inscripto->trashed()) {
                $inscripto->restore();
                $grupo = $this->incluirEnGrupo($request->all());

                if($request->notificar) {
                    if($inscripto->actividad->confirmacion == 1) 
                        $this->intentaEnviar(new MailInscripcionEsperarConfirmacion($inscripto), $inscripto->persona);

                    if($inscripto->actividad->confirmacion == 0 && $inscripto->actividad->pago == 1)
                        $this->intentaEnviar(new MailInscripcionFaltaPago($inscripto), $inscripto->persona);
                }

                return response('ok');
            }
        }

        //refactorizar a métodos de modelo
        $inscripto = $this->inscribir($request->all());
        $grupo = $this->incluirEnGrupo($request->all());

        if ($inscripto &&  $grupo) {

            if($request->notificar) {
                if($inscripto->actividad->confirmacion == 1)
                    $this->intentaEnviar(new MailInscripcionEsperarConfirmacion($inscripto), $inscripto->persona);

                if($inscripto->actividad->confirmacion == 0 && $inscripto->actividad->pago == 1)
                    $this->intentaEnviar(new MailInscripcionFaltaPago($inscripto), $inscripto->persona);
            }

            return response('ok');
        }

        return response('Error al guardar la Inscripción', 500);
    }

    private function incluirEnGrupo($request)
    {
        if(!array_key_exists('idGrupo', $request)) {
            $request['idGrupo'] = Grupo::where('idActividad', '=', (int)$request['idActividad'])
                ->orderBy('idGrupo')
                ->first()->idGrupo;
        }

        $arr = [
            'idPersona' => (int)$request['idPersona'],
            'idGrupo' => (int)$request['idGrupo'],
            'idActividad' => (int)$request['idActividad'],
        ];

        return GrupoRolPersona::create($arr);
    }

    private function inscribir($inscripcion)
    {
        $data = [
            'idActividad'       => (int)$inscripcion['idActividad'],
            'idPersona'         => (int)$inscripcion['idPersona'],
            'fechaInscripcion'  => Carbon::now(),
            'idPersonaModificacion' => auth()->user()->idPersona,
            'idPuntoEncuentro'  => $inscripcion['idPuntoEncuentro'],
            'rol'               => array_key_exists('rol', $inscripcion) ? $inscripcion['rol'] : '',
            'pago'              => empty($inscripcion['pago']) ? 0 : $inscripcion['pago'],
            'presente'          => empty($inscripcion['presente']) ? 0 : $inscripcion['presente'],
        ];

        return Inscripcion::create($data);
    }

    public function procesarArchivo($id, CrearInscripcion $request)
    {

        $actividad = Actividad::find($id);
        if ($actividad->idPais !== auth()->user()->idPaisPermitido){
            Session::flash('error', 'No tiene permisos.');
            return redirect()->back();
        }
        $this->validate($request, array(
            'archivo' => 'required'
        ));

        if($request->has('archivo')){
            $extension = File::extension($request->archivo->getClientOriginalName());
            if (in_array($extension, array("xlsx", "xls", "csv"))) {
                $path = $request->archivo->getRealPath();
                $data = (new FastExcel)->import($path);
                $procesados = 0;
                if(!empty($data) && $data->count()){
                    //Borra logs del procesamiento anterior
                    DB::table('atl_logs')
                        ->where('idPersona', auth()->user()->idPersona)
                        ->where('nombreProceso', 'importar_inscripciones')
                        ->delete();
                    $counter = 1;
                    $errores = [];
                    foreach($data as $inscripcion){
                        $errorEnRegistro = false;
                        $counter++;
                        try {
                            $persona = Persona::where('mail', $inscripcion['email'])->firstOrFail();
                        } catch(ModelNotFoundException $e) {
                            $errores[] = "Error en linea " . $counter . ". Mail no encontrado";
                            $errorEnRegistro = true;
                        }

                        try {
                            $punto = PuntoEncuentro::where('punto', $inscripcion['punto de encuentro'])
                                ->where('idActividad', $id)
                                ->firstOrFail();
                        } catch (ModelNotFoundException $e) {
                            $errores[] = "Error en linea " . $counter . ". Punto de encuentro no encontrado";
                            $errorEnRegistro = true;
                        }

                        try {
                            $grupo = Grupo::where('nombre', $inscripcion['grupo'])
                                ->where('idActividad', $id)
                                ->firstOrFail();
                        } catch (ModelNotFoundException $e) {
                            if(empty($inscripcion['grupo'])){
                                $grupo = "";
                            } else {
                                $errores[] = "Error en linea " . $counter . ". Grupo no encontrado. Corregir o dejar en blanco para asignarlo al grupo raíz.";
                                $errorEnRegistro = true;
                            }
                        }

                        if (!in_array(strtolower($inscripcion['presente']), ['no', 'si']) ) {
                            $errores[] = "Error en linea " . $counter . ". el valor de la columna presente debe ser 'si' o 'no'.";
                            $errorEnRegistro = true;
                        }

                        if (!in_array(strtolower($inscripcion['pago']), ['no', 'si']) ) {
                            $errores[] = "Error en linea " . $counter . ". el valor de la columna pago debe ser 'si' o 'no'.";
                            $errorEnRegistro = true;
                        }

                        if (!in_array(strtolower($inscripcion['confirma']), ['no', 'si']) ) {
                            $errores[] = "Error en linea " . $counter . ". el valor de la columna confirma debe ser 'si' o 'no'.";
                            $errorEnRegistro = true;
                        }

                        if(!$errorEnRegistro) {
                            $inscripcionValida = [
                                'idActividad' => $id,
                                'idPersona' => $persona->idPersona,
                                'idPuntoEncuentro' => $punto->idPuntoEncuentro,
                                'idGrupo' => ($grupo instanceof Grupo) ? $grupo->idGrupo : $actividad->grupoRaiz->idGrupo,
                                'rol' => $inscripcion['rol'],
                                'confirma' => strtolower($inscripcion['confirma']) ==='si' ? 1 : 0,
                                'pago' => strtolower($inscripcion['pago']) ==='si' ? 1 : 0,
                                'presente' => strtolower($inscripcion['presente']) ==='si' ? 1 : 0
                            ];

                            $inscripcion = $persona->noEstaInscripto($id);
                            if (!$inscripcion) {

                                $inscripto = $this->inscribir($inscripcionValida);

                                if ($inscripto) { //Enviar mail al voluntario
                                    //$this->intentaEnviar(Mail::to($persona->mail), new MailConfimacionInscripcion($inscripto), $persona);
                                } else {
                                    $errores[] = "Error interno al inscribir a "
                                        . $persona->nombreCompleto
                                        . "("
                                        . $persona->mail. ")";
                                }

                                $incluidoEnGrupo = $this->incluirEnGrupo($inscripcionValida);

                                if (empty($incluidoEnGrupo)){
                                    $errores[] = "Error interno al incluir a "
                                        . $persona->nombreCompleto
                                        . "("
                                        . $persona->mail. ") en el grupo "
                                        . $inscripcion['grupo'];
                                }

                            } else {
                                $inscripto = $inscripcion->update(
                                    [
                                        'pago'      => $inscripcionValida['pago'],
                                        'confirma'    => $inscripcionValida['confirma'],
                                        'presente'  => $inscripcionValida['presente'],
                                        'idPuntoEncuentro' => $inscripcionValida['idPuntoEncuentro'],
                                        'rol' => $inscripcionValida['rol'],
                                    ]
                                );

                                $incluidoEnGrupo = GrupoRolPersona::where('idActividad', $id)
                                    ->where('idPersona', $persona->idPersona)
                                    ->update(
                                        [
                                            'idGrupo' => $inscripcionValida['idGrupo'],
                                        ]
                                    );
                            }

                            if($inscripto && $incluidoEnGrupo) {
                                $procesados++;
                            }

                        }
                    } //end foreach

                    foreach ($errores as $error){
                        Log::create([
                            'idPersona' => auth()->user()->idPersona,
                            'nombreProceso' => 'importar_inscripciones',
                            'detalle' => $error
                        ]);
                    }
                    return response()->json(
                        [
                            'mensaje' => "Se procesaron " . $procesados . " registros de " . --$counter . " correctamente."
                                           . " Se encontraron " . count($errores) . " errores." ,
                            'log_link' => route('logs', 'importar_inscripciones'),
                            'errores' => count($errores)
                        ], 200);
                } //end if hay datos
                return response()->json([
                    'mensaje' => "No hay datos para procesar"
                ], 200);
            }
            return response()->json([
                'mensaje' => "Extensión de archivo inválida"
            ], 422);
        }
    }
}
