<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Actividad;
use App\EvaluacionActividad;
use App\EvaluacionPersona;
use App\Http\Controllers\BaseController;
use App\Inscripcion;
use App\Mail\InvitacionEvaluacion;
use App\Persona;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class EvaluacionesController extends BaseController
{
    public function enviar($id, Request $request)
    {
        $actividad = Actividad::findOrFail($id);
        $inscripciones = Inscripcion::where('Inscripcion.idActividad', '=', $id)
            ->where('Inscripcion.presente', '=', '1')
            ->get();

        foreach ($inscripciones as $i) {
            $this->intentaEnviar(new InvitacionEvaluacion($i->persona, $actividad), $i->persona);
        }
        return $inscripciones->count();
    }

    public function getActividadStats($id)
    {
        $actividad = Actividad::find($id);
        $evaluaron = $actividad->evaluaciones->count();
        $promedio = round($actividad->evaluaciones()->whereNotNull('puntaje')->avg('puntaje'),2);
        return response()->json([
            'evaluaron' => $evaluaron,
            'promedio' => $promedio,
            'presentes' => $actividad->cantidadPresentes,
        ]);

    }

    public function getActividadChartData($id)
    {
        $query = (new EvaluacionActividad)->newQuery();
        $chartData = $query->where('idActividad', $id)
            ->groupBy('puntaje')
            ->selectRaw('puntaje, count(*) as cantidad')
            ->get();

        $chartData = $chartData->toArray();
        $puntajes = [1,2,3,4,5,6,7,8,9,10];
        $puntajes = array_fill_keys($puntajes, 0);

        foreach ($chartData as $item){
            $puntajes[$item['puntaje']] = $item['cantidad'];
        }

        return response()->json(
            [
                'cantidades' => array_values($puntajes)
            ],
            200
        );
    }

    public function getVoluntariosStats($id)
    {
        $actividad = Actividad::find($id);
        $evaluaron = $actividad->evaluacionesVoluntarios()->distinct('idEvaluador')->count('idEvaluador');
        $promedioSocial = round($actividad->evaluacionesVoluntarios()->whereNotNull('puntajeSocial')->avg('puntajeSocial'),2);
        $promedioTecnico = round($actividad->evaluacionesVoluntarios()->whereNotNull('puntajeTecnico')->avg('puntajeTecnico'),2);

        return response()->json(
            [
                'evaluaron' => $evaluaron,
                'presentes' => $actividad->cantidadPresentes,
                'promedioSocial' => $promedioSocial,
                'promedioTecnico' => $promedioTecnico
            ]
        );
    }

    public function getVoluntariosChartData($id)
    {
        $query = (new EvaluacionPersona())->newQuery();
        $dataSocial = $query->where('idActividad', $id)
            ->groupBy('puntajeSocial')
            ->selectRaw('puntajeSocial, count(*) as cantidad')
            ->get();

        $query = (new EvaluacionPersona())->newQuery();
        $dataTecnico = $query->where('idActividad', $id)
            ->groupBy('puntajeTecnico')
            ->selectRaw('puntajeTecnico, count(*) as cantidad')
            ->get();

        $dataSocial = $dataSocial->toArray();
        $dataTecnico = $dataTecnico->toArray();
        $puntajesKeys = [1,2,3,4,5,6,7,8,9,10];
        $puntajesSocial = array_fill_keys($puntajesKeys, 0);
        $puntajesTecnico = array_fill_keys($puntajesKeys, 0);

        foreach ($dataSocial as $item){
            $puntajesSocial[$item['puntajeSocial']] = $item['cantidad'];
        }

        foreach ($dataTecnico as $item){
            $puntajesTecnico[$item['puntajeTecnico']] = $item['cantidad'];
        }

        return response()->json(
            [
                'cantidadesSocial' => array_values($puntajesSocial),
                'cantidadesTecnico' => array_values($puntajesTecnico)
            ],
            200
        );
    }

    public function getGeneralStats($id)
    {
        $actividad = Actividad::find($id);
        $presentes = $actividad->inscripciones()->presente()->count();
        $inscriptos = $actividad->inscripciones()->count();

        return response()->json(
            [
                'presentes' => $presentes,
                'inscriptos' => $inscriptos
            ]
        );
    }

    public function getStatsPorUsuario($id)
    {
        $persona = Persona::findOrFail($id);
        $inscripciones = $persona->inscripciones()->count();
        $presentes = $persona->inscripciones()->presente()->count();
        $ausentes = $persona->inscripciones()->ausente()->count();

        return response()->json(
            [
                'inscripciones' => $inscripciones,
                'presentes' => $presentes,
                'ausentes' => $ausentes,
            ]
        );
    }
}
