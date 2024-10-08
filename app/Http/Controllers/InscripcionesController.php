<?php

namespace App\Http\Controllers;

use App\Actividad;
use App\FichaMedica;
use App\Grupo;
use App\GrupoRolPersona;
use App\Inscripcion;
use App\Mail\MailInscripcionConfirmada;
use App\Mail\MailInscripcionEsperarConfirmacion;
use App\Mail\MailInscripcionFaltaPago;
use App\PuntoEncuentro;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class InscripcionesController extends BaseController
{
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
            ->with('punto_encuentro', $puntoEncuentro)
            ->with('roles_aplicados', $request->input('roles_aplicados'))
            ->with('inscripciones_aplicadas', $request->input('inscripciones_aplicadas'))
            ->with('aplica_rol', $request->input('aplica_rol'))
            ->with('jornadas', $request->input('jornadas'))
            ->with('jornadasSelected', json_decode($request->input('jornadas'), true))
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
        $request->validate([
            'roles_aplicados' => 'json',
            'inscripciones_aplicadas' => 'json',
            'jornadas' => 'json',
        ]);
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
                $inscripcion->roles_aplicados = $request->input('roles_aplicados');
                $inscripcion->inscripciones_aplicadas = $request->input('inscripciones_aplicadas');

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
                $this->intentaEnviar(new MailInscripcionEsperarConfirmacion($inscripcion), Auth::user());
                return view('inscripciones.confirmar-paso-1')
                    ->with('actividad', $actividad);
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
                $this->intentaEnviar(new MailInscripcionFaltaPago($inscripcion), Auth::user());

                return view('inscripciones.pagar-paso-1')
                    ->with('actividad', $actividad)
                    ->with('inscripcion', $inscripcion)
                    ->with('payment', $payment);
            }

            $inscripcion->save();
            $this->intentaEnviar(new MailInscripcionConfirmada($inscripcion), Auth::user());
            return view('inscripciones.gracias')
                ->with('actividad', $actividad);
        }
        $request->session()->flash('status', 'Debe aceptar los términos para continuar');
        return view('inscripciones.confirmar')
            ->with('actividad', $actividad)
            ->with('punto_encuentro', $punto_encuentro)
            ->with('tipo', $actividad->tipo);
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
