<?php

namespace App\Http\Controllers\ajax;

use App\Http\Controllers\Controller;
use App\Integrante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class EquiposController extends Controller
{
    public function update(Request $request) {
        $persona = Auth::user();
        $validados = $request->validate([
            'idIntegrante' => 'required',
            'descripcion_rol' => 'required',
            'meta' => 'required',
            'hitos' => 'required',
            'dia_hora_reunion' => 'required',
            'periodicidad_reunion' => 'required',
        ]);

        $integrante = Integrante::find($validados['idIntegrante']);

        if($integrante->idPersona == $persona->idPersona)
            $integrante->update($validados);


        return response()->json($integrante);
    }

    public function updateCartaCompromiso(Request $request) {
        $validados = $this->validate($request, array(
            'carta_compromiso' => 'required',
            'integrante_id' => 'required',
        ));
    
        $persona = Auth::user();
        $integrante = Integrante::find($validados['integrante_id']);
    
        if ($request->file('carta_compromiso')){
          $archivo = $request->file('carta_compromiso');
          $path = $archivo->store('public/perfil/equipos');
          $oldPath = str_replace('storage', 'public', $integrante->archivo_carta_compromiso);
          if(Storage::exists($oldPath))
              Storage::delete($oldPath);
    
          $integrante->archivo_carta_compromiso = str_replace('public', 'storage', $path);
          $integrante->save();
        }

    }

}
