<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Actividad;
use App\Exports\InscripcionesExport;
use App\Search\InscripcionesSearch;
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
use App\Mail\MailVoucherRechazado;
use App\Persona;
use App\PuntoEncuentro;
use App\Services\Listados\EnriquecedorFilas;
use App\Services\Push\PushNotificationService;
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
    protected $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        $this->pushService = $pushService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id, Request $request)
    {
        // Traducción request → filtros (búsqueda libre + condiciones avanzadas)
        // + metadata de campos filtrables, compartida con recuento/facets.
        $filtros = (new \App\Services\Listados\ListadoQuery())
            ->filtrosDesdeRequest($request, 'inscripciones', $id);

        $result = InscripcionesSearch::query($filtros)->paginate(10);

        // Inyecta respuestas a preguntas (pregunta_{id}) y valores de columnas
        // de seguimiento (custom_{id}) solo sobre la página actual.
        $enriquecedor = new EnriquecedorFilas;
        $enriquecedor->enriquecer($result->getCollection(), 'inscripciones', $id, $id);
        // Métricas por voluntario para las columnas opcionales (participaciones,
        // nivel, evaluación general).
        $enriquecedor->inyectarMetricasVoluntario($result->getCollection(), 'idPersona');

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
                if($inscripcion->actividad->confirmacion == 1 && $inscripcion->actividad->pago == 0) {
                    $this->intentaEnviar(new MailInscripcionConfirmada($inscripcion), $inscripcion->persona);
                    $this->pushService->enviarLocalizado(
                        $inscripcion->persona,
                        'push.inscripcion_confirmada_titulo',
                        'push.inscripcion_confirmada_cuerpo',
                        ['actividad' => $inscripcion->actividad->nombreActividad],
                        ['tipo' => 'inscripcion', 'estado' => 'CONFIRMADO', 'idActividad' => $inscripcion->actividad->idActividad]
                    );
                }

                if($inscripcion->actividad->confirmacion == 1 && $inscripcion->actividad->pago == 1) {
                    $this->intentaEnviar(new MailInscripcionFaltaPago($inscripcion), $inscripcion->persona);
                    $this->pushService->enviarLocalizado(
                        $inscripcion->persona,
                        'push.pago_pendiente_titulo',
                        'push.pago_pendiente_cuerpo',
                        ['actividad' => $inscripcion->actividad->nombreActividad],
                        ['tipo' => 'inscripcion', 'estado' => 'FALTA_PAGO', 'idActividad' => $inscripcion->actividad->idActividad]
                    );
                }
            }

            $inscripcion->confirma = $request->confirma;
        }

        if($request->has('pago')){
            if($inscripcion->actividad->pago == 1 && $request->pago == 1) {
                $this->intentaEnviar(new MailInscripcionConfirmada($inscripcion), $inscripcion->persona);
                $this->pushService->enviarLocalizado(
                    $inscripcion->persona,
                    'push.pago_exitoso_titulo',
                    'push.pago_exitoso_cuerpo',
                    ['actividad' => $inscripcion->actividad->nombreActividad],
                    ['tipo' => 'inscripcion', 'estado' => 'PAGO_CONFIRMADO', 'idActividad' => $inscripcion->actividad->idActividad]
                );
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

    public function desinscribir(CrearInscripcion $request, $id)
    {
        $ids = collect($request->inscripciones)
            ->map(function ($v) { return (int) $v; })
            ->filter()
            ->unique()
            ->values();

        // Solo se borran inscripciones que REALMENTE pertenecen a esta actividad.
        // El front (selectedTo de vuetable-2) arrastra selección entre páginas,
        // filtros y otras acciones masivas; sin este cerco, un id colado borraba
        // la inscripción de un tercero de otra actividad (bug de desinscripción
        // "aleatoria"). Además, el hook `deleting` de Inscripcion audita cada baja.
        $inscripciones = Inscripcion::where('idActividad', $id)
            ->whereIn('idInscripcion', $ids)
            ->get();

        foreach ($inscripciones as $inscripcion) {
            $inscripcion->delete();
        }

        $descartadas = $ids->count() - $inscripciones->count();
        $msg = $inscripciones->count() . " inscripciones eliminadas.";
        if ($descartadas > 0) {
            $msg .= " ({$descartadas} ignoradas por no pertenecer a la actividad)";
        }

        return response()->json($msg, 200);
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
            $this->pushService->enviarLocalizado(
                $inscripcion->persona,
                'push.cambio_actividad_titulo',
                'push.cambio_actividad_cuerpo',
                ['actividad' => $inscripcion->actividad->nombreActividad],
                ['tipo' => 'actividad', 'estado' => 'CAMBIO', 'idActividad' => $inscripcion->actividad->idActividad]
            );
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

            if ($request->confirmacion == 1) {
                if ($inscripcion->actividad->pago == 1) {
                    $this->pushService->enviarLocalizado(
                        $inscripcion->persona,
                        'push.pago_pendiente_titulo',
                        'push.pago_pendiente_cuerpo',
                        ['actividad' => $inscripcion->actividad->nombreActividad],
                        ['tipo' => 'inscripcion', 'estado' => 'FALTA_PAGO', 'idActividad' => $inscripcion->actividad->idActividad]
                    );
                } else {
                    $this->pushService->enviarLocalizado(
                        $inscripcion->persona,
                        'push.inscripcion_confirmada_titulo',
                        'push.inscripcion_confirmada_cuerpo',
                        ['actividad' => $inscripcion->actividad->nombreActividad],
                        ['tipo' => 'inscripcion', 'estado' => 'CONFIRMADO', 'idActividad' => $inscripcion->actividad->idActividad]
                    );
                }
            }
        }

        $msg = $request->confirmacion == 1 ? "Confirmado" : "Sin Confirmar";
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

            if ($request->pago == 1) {
                $this->pushService->enviarLocalizado(
                    $inscripcion->persona,
                    'push.pago_exitoso_titulo',
                    'push.pago_exitoso_cuerpo',
                    ['actividad' => $inscripcion->actividad->nombreActividad],
                    ['tipo' => 'inscripcion', 'estado' => 'PAGO_CONFIRMADO', 'idActividad' => $inscripcion->actividad->idActividad]
                );
            }
        }

        $msg = $request->pago == 1 ? "Pagado" : "Sin Pagar";
        return response()
            ->json("Asistencia actualizada a " . $msg . " en " . count($request->inscripciones) . " voluntarios correctamente.", 200);
    }

    public function rechazarVoucher(Request $request, $id)
    {
        $request->validate([
            'idInscripcion' => 'required|integer',
            'motivo'        => 'nullable|string|max:1000',
        ]);

        $inscripcion = Inscripcion::where('idActividad', $id)
            ->where('idInscripcion', $request->idInscripcion)
            ->firstOrFail();

        $motivo = $request->input('motivo');

        $inscripcion->voucher_rechazado    = true;
        $inscripcion->voucher_rechazo_motivo = $motivo;
        $inscripcion->save();

        try {
            Mail::to($inscripcion->persona->mail)->send(new MailVoucherRechazado($inscripcion, $motivo));
        } catch (\Exception $e) {
            \Log::warning('No se pudo enviar mail de rechazo de voucher: ' . $e->getMessage());
        }

        return response()->json(['mensaje' => 'Comprobante rechazado y usuario notificado.'], 200);
    }

    public function rechazarDocumento(Request $request, $id)
    {
        $request->validate([
            'idInscripcion' => 'required|integer',
            'motivo'        => 'nullable|string|max:1000',
        ]);

        $inscripcion = Inscripcion::where('idActividad', $id)
            ->where('idInscripcion', $request->idInscripcion)
            ->firstOrFail();

        // La ficha médica es por persona: el rechazo del documento aplica a la
        // persona en todas sus actividades.
        $ficha = \App\FichaMedica::where('idPersona', $inscripcion->idPersona)->firstOrFail();

        $motivo = $request->input('motivo');
        $ficha->documento_rechazado = true;
        $ficha->documento_rechazo_motivo = $motivo;
        $ficha->save();

        try {
            Mail::to($inscripcion->persona->mail)->send(new \App\Mail\MailDocumentoRechazado($inscripcion, $motivo));
        } catch (\Exception $e) {
            \Log::warning('No se pudo enviar mail de rechazo de documento: ' . $e->getMessage());
        }

        return response()->json(['mensaje' => 'Documento rechazado y usuario notificado.'], 200);
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
}
