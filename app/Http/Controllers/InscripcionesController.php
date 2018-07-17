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
        if($request->has('aceptar_terminos') && $request->aceptar_terminos == 1){
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
