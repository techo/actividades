<?php

namespace App\Http\Controllers\ajax;

use App\Http\Controllers\Controller;
use App\Estudios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;


class EstudiosController extends Controller
{
    public function create(Request $request) {
        $estudio = $request->validate([
            'institucion_educativa' => 'required',
            'titulo' => 'required',
            'disciplina_academica' => 'required',
            'descripcion_educacion' => 'required',
            'idPersona' => 'required',
        ]);

        $persona = Auth::user();
        if($estudio['idPersona'] == $persona->idPersona)
            Estudios::create($estudio);

        return response()->json($estudio);
    }

    public function update(Request $request) {
        $persona = Auth::user();
        $validados = $request->validate([
            'id' => 'required',
            'institucion_educativa' => 'required',
            'titulo' => 'required',
            'disciplina_academica' => 'required',
            'descripcion_educacion' => 'required',
        ]);

        $estudio = Estudios::find($validados['id']);

        if($estudio->idPersona == $persona->idPersona)
            $estudio->update($validados);


        return response()->json($estudio);
    }

    public function delete(Estudios $id)
    {
        $persona = Auth::user();
        if($id->idPersona == $persona->idPersona){
            if ($id->delete()){
                Session::flash('mensaje', 'Estudio eliminado correctamente');
            } else {
                Session::flash('mensaje', 'Ocurrio un error al querer eliminar a el estudio');
            }
        }
    }
}
