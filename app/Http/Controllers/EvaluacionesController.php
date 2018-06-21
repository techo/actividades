<?php

namespace App\Http\Controllers;

use App\Actividad;
use Illuminate\Http\Request;

class EvaluacionesController extends Controller
{
    public function index($id)
    {
        $actividad = Actividad::findOrFail($id);
        return view('evaluaciones.index', compact('actividad'));
    }
}
