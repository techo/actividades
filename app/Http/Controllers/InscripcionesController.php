<?php

namespace App\Http\Controllers;

use App\Grupo;
use App\GrupoRolPersona;
use Illuminate\Http\Request;
use App\Actividad;
use App\PuntoEncuentro;
use App\Inscripcion;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailConfimacionInscripcion;

class InscripcionesController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function confirmar(Request $request, $id)
    {
        $actividad = Actividad::find($id);
        $actividad->descripcion = clean_string($actividad->descripcion);
        $idPuntoEncuentro = $request->input('punto_encuentro');
        $puntoEncuentro = PuntoEncuentro::find($idPuntoEncuentro);
        $tipo = $actividad->tipo;
        return view('inscripciones.confirmar')
            ->with('actividad', $actividad)
            ->with('punto_encuentro', $puntoEncuentro)
            ->with('tipo', $tipo);

    }

    /**
     * @param Request $request
     * @param $id
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function create(Request $request, $id)
    {
        $actividad = Actividad::find($id);
        $actividad->load('pais','provincia','localidad');
        $punto_encuentro = PuntoEncuentro::find($request->input('punto_encuentro'));
        $punto_encuentro->load('pais','provincia','localidad');
        $persona = Auth::user();
        if (($request->has('aceptar_terminos') && $request->aceptar_terminos == 1)) {
            $inscripcion = Inscripcion::where([['idActividad', $id], ['idPersona', Auth::user()->idPersona]])->whereNotIn('estado',['Desinscripto'])->get()->first();
            if (!$inscripcion) {
                $inscripcion = new Inscripcion();
                $inscripcion->idActividad = $id;
                $inscripcion->idPuntoEncuentro = $request->input('punto_encuentro');
                $inscripcion->idPersona = Auth::user()->idPersona;
                $inscripcion->evaluacion = 0;
                $inscripcion->acompanante = '';
                $inscripcion->fechaInscripcion = new Carbon();

                $this->incluirEnGrupoRaiz($actividad, $persona->idPersona);
            }

            if (strtoupper($actividad->tipo->flujo) === 'CONSTRUCCION') {
                try {
                    $config = json_decode($actividad->pais->config_pago);
                    $paymentClass = 'App\\Payments\\' . $config->payment_class;
                    $payment = new $paymentClass($inscripcion);
                } catch (\Exception $e) {
                    return response('La configuración de pagos de '. $actividad->pais->nombre .' no está establecida', 500);
                }
                $payment->setMonto($request->monto);
                $inscripcion->estado = 'Pre-Inscripto';
                $inscripcion->save();
                Mail::to(Auth::user()->mail)->send(new MailConfimacionInscripcion($inscripcion));

                return view('inscripciones.pagar')
                    ->with('actividad', $actividad)
                    ->with('payment', $payment);
            }
            $inscripcion->estado = 'Sin Contactar';
            $inscripcion->save();
            Mail::to(Auth::user()->mail)->send(new MailConfimacionInscripcion($inscripcion));
            return view('inscripciones.gracias')
                ->with('actividad', $actividad);
        }
        $request->session()->flash('status', 'Debe aceptar los términos para continuar');
        return view('inscripciones.confirmar')
            ->with('actividad', $actividad)
            ->with('punto_encuentro', $punto_encuentro)
            ->with('tipo', $actividad->tipo);
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
        return view('inscripciones.seleccionar_puntos_encuentro')->with('actividad', $actividad);
    }

    public function confirmarDonacion($id)
    {
        $actividad = Actividad::find($id);
        $inscripcion = Inscripcion::where('idPersona', auth()->user()->idPersona)
            ->where('idActividad', $actividad->idActividad)
            ->where('estado', 'Pre-inscripto')
            ->first();
        $punto_encuentro = PuntoEncuentro::find($inscripcion->idPuntoEncuentro);
        return view('inscripciones.confirmar')
            ->with('actividad', $actividad)
            ->with('punto_encuentro', $punto_encuentro)
            ->with('tipo', $actividad->tipo);


    }

    /**
     * @param Actividad $idActividad
     * @param int $idPersona
     * @return GrupoRolPersona
     */
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

    /**
     * @param Request $request
     * @param $id
     * @param $actividad
     * @param $persona
     * @return Inscripcion
     */
    public function crearInscripcion(Request $request, $id, $actividad, $persona): Inscripcion
    {
        $inscripcion = new Inscripcion();
        $inscripcion->idActividad = $id;
        $inscripcion->idPuntoEncuentro = $request->input('punto_encuentro');
        $inscripcion->idPersona = $persona->idPersona;
        $inscripcion->evaluacion = 0;
        $inscripcion->acompanante = '';
        $inscripcion->fechaInscripcion = new Carbon();
        $inscripcion->estado = 'Sin Contactar';

        $this->incluirEnGrupoRaiz($actividad, $persona->idPersona);
        $inscripcion->save();
        Mail::to(Auth::user()->mail)->send(new MailConfimacionInscripcion($inscripcion));
        return $inscripcion;
    }

}
