<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\ActividadPregunta;
use App\Actividad;
use App\Http\Controllers\Concerns\ManagesPreguntas;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ActividadPreguntasController extends Controller
{
    use ManagesPreguntas;

    protected function preguntaClass()
    {
        return ActividadPregunta::class;
    }

    protected function ownerColumn()
    {
        return 'actividad_id';
    }

    public function index(Actividad $actividad)
    {
        return $this->respondPreguntas($actividad->idActividad);
    }

    public function store(Request $request, Actividad $actividad)
    {
        return $this->crearPregunta($request, $actividad->idActividad);
    }

    public function update(Request $request, Actividad $actividad, $preguntaId)
    {
        return $this->actualizarPregunta($request, $actividad->idActividad, $preguntaId);
    }

    public function destroy(Actividad $actividad, $preguntaId)
    {
        return $this->eliminarPregunta($actividad->idActividad, $preguntaId);
    }

    public function mover(Request $request, Actividad $actividad, $preguntaId)
    {
        return $this->moverPregunta($request, $actividad->idActividad, $preguntaId);
    }
}
