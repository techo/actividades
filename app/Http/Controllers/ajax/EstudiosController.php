<?php

namespace App\Http\Controllers\ajax;

use App\Http\Controllers\Controller;
use App\Estudios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;


class EstudiosController extends Controller
{
    public function create(Request $request) {
        $estudio = $request->validate([
            'institucion_educativa' => 'required',
            'idInstitucionEducativa' => 'nullable',
            'disciplina_academica' => 'required',
            'descripcion_educacion' => 'nullable',
            'nivelDeEstudios' => 'nullable',
            'idPaisInstitucion' => 'nullable',
            'idPersona' => 'required',
        ]);

        $persona = Auth::user();
        if($estudio['idPersona'] == $persona->idPersona)
            Estudios::create($estudio);

        return response()->json($estudio);
    }

    public function update(Request $request) {
        
        $validados = $request->validate([
            'id' => 'required',
            'institucion_educativa' => 'required',
            'idInstitucionEducativa' => 'nullable',
            'disciplina_academica' => 'required',
            'descripcion_educacion' => 'nullable',
            'nivelDeEstudios' => 'nullable',
            'idPaisInstitucion' => 'nullable',
        ]);
        $idPersona = Auth::user()->idPersona;
        $estudio = Estudios::findOrFail($validados['id']);

        if($estudio->persona->idPersona == $idPersona)
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

    public function estudiosUsuario()
    {
        $total = Estudios::where('idPersona', '=', auth()->user()->idPersona)->count(); 

        return response()->json([
            'total' => $total
        ]);
    }
}
