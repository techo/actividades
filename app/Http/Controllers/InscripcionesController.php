<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Actividad;
use App\PuntoEncuentro;
use App\Inscripcion;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Mail;
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
        $idPuntoEncuentro = $request->input('punto_encuentro');
        $puntoEncuentro = PuntoEncuentro::find($idPuntoEncuentro);
        return view('inscripciones.confirmar')->with('actividad', $actividad)->with('punto_encuentro', $puntoEncuentro);

    }

    /**
     * @param Request $request
     * @param $id
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function create(Request $request, $id)
    {
        $actividad = Actividad::find($id);
        $punto_encuentro = PuntoEncuentro::find($request->input('punto_encuentro'));
        $persona = Auth::user();
        $inscripcion = Inscripcion::where([['idActividad', $id], ['idPersona', Auth::user()->idPersona]])->whereNotIn('estado',['Desinscripto'])->get()->first();
        if (!$inscripcion) {
            $inscripcion = new Inscripcion();
            $inscripcion->idActividad = $id;
            $inscripcion->idPuntoEncuentro = $request->input('punto_encuentro');
            $inscripcion->idPersona = Auth::user()->idPersona;
            $inscripcion->evaluacion = 0;
            $inscripcion->acompanante = '';
            $inscripcion->estado = 'Sin Contactar';
            $inscripcion->fechaInscripcion = new Carbon();
            $inscripcion->save();
            Mail::to(Auth::user()->mail)->send(new MailConfimacionInscripcion($inscripcion));
            return view('inscripciones.gracias')->with('actividad', $actividad)->with('punto_encuentro', $punto_encuentro);
        }
        return view('inscripciones.gracias')->with('actividad', $actividad)->with('punto_encuentro', $punto_encuentro);
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
}
