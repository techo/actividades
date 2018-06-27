<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Actividad;
use App\Inscripcion;
use App\Mail\InvitacionEvaluacion;
use App\Persona;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class EvaluacionesController extends Controller
{
    public function enviar($id, Request $request)
    {
        $actividad = Actividad::findOrFail($id);
        $personas = Persona::join('Inscripcion', 'Persona.idPersona', '=', 'Inscripcion.idPersona')
            ->where('Inscripcion.idActividad', '=', $id)
            ->where('Inscripcion.presente', '=', '1')
            ->get();

        foreach ($personas as $persona) {
            Mail::to($persona->mail)->queue(new InvitacionEvaluacion($persona, $actividad));
        }
        return 'ok';
    }
}
