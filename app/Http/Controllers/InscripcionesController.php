<?php

namespace App\Http\Controllers;

use App\Actividad;
use App\FichaMedica;
use App\Grupo;
use App\GrupoRolPersona;
use App\Inscripcion;
use App\InscripcionRespuesta;
use App\Mail\MailInscripcionConfirmada;
use App\Mail\MailInscripcionEsperarConfirmacion;
use App\Mail\MailInscripcionFaltaPago;
use App\PuntoEncuentro;
use App\Services\InscripcionFlow;
use App\Services\Push\PushNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class InscripcionesController extends BaseController
{
    protected $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        $this->pushService = $pushService;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function confirmar(Request $request, $id)
    {
        $request->validate([
            'roles_aplicados' => 'json',
            'inscripciones_aplicadas' => 'json',
            'jornadas' => 'json',
            'respuestas' => 'nullable|json',
        ]);
        $actividad = Actividad::find($id);
        $actividad->descripcion = clean_string($actividad->descripcion);
        $idPuntoEncuentro = $request->input('punto_encuentro');
        $puntoEncuentro = PuntoEncuentro::find($idPuntoEncuentro);
        $tipo = $actividad->tipo;

        $currentDate = Carbon::now();
        $edad = $currentDate->diffInYears(Carbon::parse(Auth::user()->fechaNacimiento));
        $jornadas = json_decode($request->input('jornadas'), true);
        return view('inscripciones.confirmar')
            ->with('actividad', $actividad)
            ->with('flowSteps', InscripcionFlow::stepsWithState($actividad, 'confirmar', 'blade'))
            ->with('punto_encuentro', $puntoEncuentro)
            ->with('roles_aplicados', $request->input('roles_aplicados'))
            ->with('inscripciones_aplicadas', $request->input('inscripciones_aplicadas'))
            ->with('aplica_rol', $request->input('aplica_rol'))
            ->with('jornadas', $request->input('jornadas'))
            ->with('jornadasSelected', json_decode($request->input('jornadas'), true))
            ->with('respuestas', $request->input('respuestas', '[]'))
            ->with('tipo', $tipo)
            ->with('edad', $edad);

    }

    /**
     * @param Request $request
     * @param $id
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function create(Request $request, $id)
    {
        $validated = $request->validate([
            'roles_aplicados' => 'nullable|json',
            'inscripciones_aplicadas' => 'nullable|json',
            'jornadas' => 'nullable|json',
            'respuestas' => 'nullable|json',
        ]);

        $inscripciones = json_decode($validated['inscripciones_aplicadas'] ?? '[]', true);

        $validated['inscripciones_aplicadas'] = collect($inscripciones)
            ->map(function ($item) {
                return is_array($item) && isset($item['id'])
                    ? $item['id']
                    : $item;
            })
            ->values()
            ->toArray();
        
        $roles = json_decode($validated['roles_aplicados'] ?? '[]', true);

        $validated['roles_aplicados'] = collect($roles)
            ->map(function ($item) {
                return is_array($item) && isset($item['id'])
                    ? $item['id']
                    : $item;
            })
            ->values()
            ->toArray();

        $actividad = Actividad::find($id);
        $actividad->load('pais','provincia','localidad');
        $punto_encuentro = PuntoEncuentro::find($request->input('punto_encuentro'));
        $punto_encuentro->load('pais','provincia','localidad');
        $persona = Auth::user();
        if ($punto_encuentro->estado)
        {
        if (($request->has('aceptar_terminos') && $request->aceptar_terminos == 1)) {
            $inscripcion = Inscripcion::where([['idActividad', $id], ['idPersona', Auth::user()->idPersona]])->get()->first();
            if (!$inscripcion) {
                $inscripcion = new Inscripcion();
                $inscripcion->idActividad = $id;
                $inscripcion->idPuntoEncuentro = $request->input('punto_encuentro');
                $inscripcion->idPersona = Auth::user()->idPersona;
                $inscripcion->fechaInscripcion = new Carbon();
                $inscripcion->roles_aplicados = $validated['roles_aplicados'];
                $inscripcion->inscripciones_aplicadas = $validated['inscripciones_aplicadas'];

                $this->incluirEnGrupoRaiz($actividad, $persona->idPersona);

                
            }

            $jornadas = json_decode($request->input('jornadas'), true);
            if(count($jornadas)>0){
                $inscripcion->save();
                foreach ($jornadas as $jornada) {
                    if($jornada['selected'])
                        $inscripcion->jornadas()->attach($jornada['idJornada']);
                }
            }
            
            if ($actividad->confirmacion == 1) {
                $inscripcion->save();
                $this->guardarRespuestasInscripcion($inscripcion, $request);
                $this->intentaEnviar(new MailInscripcionEsperarConfirmacion($inscripcion), Auth::user());
                $this->pushService->enviar(
                    $persona,
                    '¡Inscripción recibida!',
                    'Tu inscripción a "' . $actividad->nombreActividad . '" está pendiente de confirmación.',
                    ['tipo' => 'inscripcion', 'estado' => 'PRE_INSCRIPTO', 'idActividad' => $actividad->idActividad]
                );
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Inscripción registrada, a la espera de confirmación',
                        'actividad_id' => $actividad->idActividad,
                        'inscripcion_id' => $inscripcion->idInscripcion ?? null,
                        'estado_inscripcion' => 'PRE_INSCRIPTO',
                    ]);
                }
                return view('inscripciones.confirmar-paso-1')
                    ->with('actividad', $actividad)
                    ->with('flowSteps', InscripcionFlow::stepsWithState($actividad, 'finalizar', 'blade'));
            }

            if ($actividad->pago == 1) {
                try {
                    $config = json_decode($actividad->pais->config_pago);
                    $paymentClass = 'App\\Payments\\' . $config->payment_class;
                    $payment = new $paymentClass($inscripcion);
                } catch (\Exception $e) {
                    return response('La configuración de pagos de '. $actividad->pais->nombre .' no está establecida', 500);
                }
                $payment->setMonto($request->monto);
                $inscripcion->save();
                $this->guardarRespuestasInscripcion($inscripcion, $request);
                $this->intentaEnviar(new MailInscripcionFaltaPago($inscripcion), Auth::user());
                $this->pushService->enviar(
                    $persona,
                    '¡Ya casi estás!',
                    'Falta completar el pago para confirmar tu inscripción a "' . $actividad->nombreActividad . '".',
                    ['tipo' => 'inscripcion', 'estado' => 'FALTA_PAGO', 'idActividad' => $actividad->idActividad]
                );

                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Inscripción registrada, falta pago',
                        'estado_inscripcion' => 'FALTA_PAGO',
                        'actividad_id' => $actividad->idActividad,
                        'inscripcion_id' => $inscripcion->idInscripcion ?? null,
                    ]);
                }
                return view('inscripciones.pagar-paso-1')
                    ->with('actividad', $actividad)
                    ->with('flowSteps', InscripcionFlow::stepsWithState($actividad, 'pago', 'blade'))
                    ->with('inscripcion', $inscripcion)
                    ->with('payment', $payment);
            }

            $inscripcion->save();
            $this->guardarRespuestasInscripcion($inscripcion, $request);
            $this->intentaEnviar(new MailInscripcionConfirmada($inscripcion), Auth::user());
            $this->pushService->enviar(
                $persona,
                '¡Inscripción confirmada! 🎉',
                'Ya estás anotado en "' . $actividad->nombreActividad . '". ¡Nos vemos!',
                ['tipo' => 'inscripcion', 'estado' => 'CONFIRMADO', 'idActividad' => $actividad->idActividad]
            );
            if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Inscripción confirmada',
                        'actividad_id' => $actividad->idActividad,
                        'inscripcion_id' => $inscripcion->idInscripcion ?? null,
                        'estado_inscripcion' => 'CONFIRMADO',
                    ]);
                }
            return view('inscripciones.gracias')
                ->with('actividad', $actividad)
                ->with('flowSteps', InscripcionFlow::stepsWithState($actividad, 'finalizar', 'blade'));
        }
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Debe aceptar los términos para continuar.',
                'actividad_id' => $actividad->idActividad,
                'estado_inscripcion' => 'FALTA_ACEPTAR_TERMINOS',
            ]);
        }
        $request->session()->flash('status', 'Debe aceptar los términos para continuar');
        return view('inscripciones.confirmar')
            ->with('actividad', $actividad)
            ->with('flowSteps', InscripcionFlow::stepsWithState($actividad, 'confirmar', 'blade'))
            ->with('punto_encuentro', $punto_encuentro)
            ->with('tipo', $actividad->tipo);
        }
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Punto de Encuentro cerrado.',
                'estado_inscripcion' => 'PUNTO_ENCUENTRO_CERRADO',
                'actividad_id' => $actividad->idActividad,
            ]);
        }
        return response('El punto de encuentro se encuentra cerrado', 500);
    }

    /**
     * @param $id
     * @return array
     */
    public function inscripto($id)
    {
        if (Auth::check() && Auth::user()->estaInscripto($id)) {
            return Array('idActividad' => $id);
        }
        return Array('idActividad' => false);
    }

    /**
     * Retorna la vista para elegir el punto de encuentro de una actividad dada
     * @param $id Actividad
     * @return $this
     */
    public function puntoDeEncuentro($id)
    {
        $actividad = Actividad::find($id);

        return view('inscripciones.seleccionar_puntos_encuentro',
             compact('actividad'));
    }
    
    public function voucherPago(Request $request){

        $validated = $request->validate([
            'idInscripcion' => 'required',
            'voucher' => 'required',
        ]);

        $inscripcion = Inscripcion::where('idPersona', auth()->user()->idPersona)
        ->where('idInscripcion', $request->idInscripcion)
        ->firstOrFail();

        $archivo = $request->file('voucher');
        $path = $archivo->store('public/voucherInscipcion/'.auth()->user()->idPersona);
        $oldPath = str_replace('storage', 'public', $inscripcion->voucherURL);
        if(Storage::exists($oldPath))
            Storage::delete($oldPath);
    
        $inscripcion->voucherURL = str_replace('public', 'storage', $path);
        $inscripcion->save();
        
        return $inscripcion;
      
    }

    public function confirmarDonacion($id)
    {
        $actividad = Actividad::find($id);
        $inscripcion = Inscripcion::where('idPersona', auth()->user()->idPersona)
            ->where('idActividad', $actividad->idActividad)
            ->firstOrFail();

        try {
            $config = json_decode($actividad->pais->config_pago);
            $paymentClass = 'App\\Payments\\' . $config->payment_class;
            $payment = new $paymentClass($inscripcion);
        } catch (\Exception $e) {
            return response('La configuración de pagos de '. $actividad->pais->nombre .' no está establecida', 500);
        }

        return view('inscripciones.pagar-paso-1')
            ->with('actividad', $actividad)
            ->with('flowSteps', InscripcionFlow::stepsWithState($actividad, 'pago', 'blade'))
            ->with('inscripcion', $inscripcion)
            ->with('payment', $payment);

    }

    public function donacionCheckout(Request $request, $id)
    {
        if(!$request->has('monto') || !is_numeric($request->monto)){
            abort(403);
        }

        $actividad = Actividad::find($id);
        $inscripcion = Inscripcion::where('idPersona', auth()->user()->idPersona)
            ->where('idActividad', $actividad->idActividad)
            ->firstOrFail();


        try {
            $config = json_decode($actividad->pais->config_pago);
            $paymentClass = 'App\\Payments\\' . $config->payment_class;
            $payment = new $paymentClass($inscripcion);
        } catch (\Exception $e) {
            return response('La configuración de pagos de '. $actividad->pais->nombre .' no está establecida', 500);
        }
        $payment->setMonto($request->monto);

        return view('inscripciones.pagar-paso-2')
            ->with('actividad', $actividad)
            ->with('payment', $payment);

    }

    /**
     * @param Actividad $idActividad
     * @param int $idPersona
     * @return GrupoRolPersona
     */
    private function guardarRespuestasInscripcion(Inscripcion $inscripcion, Request $request)
    {
        $respuestasJson = $request->input('respuestas', '[]');
        $respuestas = json_decode($respuestasJson, true);

        if (!is_array($respuestas) || empty($respuestas)) return;

        foreach ($respuestas as $respuesta) {
            if (!isset($respuesta['pregunta_id'])) continue;

            InscripcionRespuesta::updateOrCreate(
                [
                    'inscripcion_id' => $inscripcion->idInscripcion,
                    'pregunta_id'    => $respuesta['pregunta_id'],
                ],
                [
                    'respuesta' => $respuesta['respuesta'] ?? null,
                ]
            );
        }
    }

    private function incluirEnGrupoRaiz(Actividad $actividad, int $idPersona)
    {
        $grupoRaiz = Grupo::firstOrCreate(
            [
                'idActividad' => $actividad->idActividad,
                'idPadre' => 0,
                'nombre' => $actividad->nombreActividad
            ]
        );
        $arr = [
            'idPersona' => $idPersona,
            'idGrupo' => $grupoRaiz->idGrupo,
            'idActividad' => $actividad->idActividad,
            'rol' => ''
        ];

        return GrupoRolPersona::create($arr);
    }

}
