<?php

namespace App\Http\Controllers\ajax;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\FichaMedica;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FichaMedicaController extends Controller
{
  

  public function upsert(Request $request) {
      $url = $request->session()->get('login_callback','');

      $request->validate([
              'contacto_nombre' => 'nullable',
              'contacto_telefono' => 'nullable',
              'contacto_relacion' => 'nullable',
              'grupo_sanguinieo' => 'nullable',
              'cobertura_tipo' => 'nullable',
              'cobertura_nombre' => 'nullable',
              'cobertura_numero' => 'nullable',
              'alergias' => 'nullable',
              'alimentacion' => 'nullable',
              'confirma_datos' => 'required',
              ]);
      $persona = Auth::user();
      $countFichas = FichaMedica::where('idPersona', $persona->idPersona)->count();

      if($countFichas>0)
        $fichaMedica = FichaMedica::where('idPersona', $persona->idPersona)->first();
      else
        $fichaMedica = FichaMedica::create(['idPersona' => $persona->idPersona]);

      $fichaMedica->contacto_nombre = $request->contacto_nombre;
      $fichaMedica->contacto_telefono = $request->contacto_telefono;
      $fichaMedica->contacto_relacion = $request->contacto_relacion;
      $fichaMedica->grupo_sanguinieo = $request->grupo_sanguinieo;
      $fichaMedica->cobertura_tipo = $request->cobertura_tipo;
      $fichaMedica->cobertura_nombre = $request->cobertura_nombre;
      $fichaMedica->cobertura_numero = $request->cobertura_numero;
      $fichaMedica->alergias = $request->alergias;
      $fichaMedica->alimentacion = $request->alimentacion;
      $fichaMedica->confirma_datos = $request->confirma_datos;

      $fichaMedica->save();
  }

  public function uploadArchivoMedico(Request $request)
  {
    $this->validate($request, array(
        'archivo_medico' => 'nullable',
        'documento_frente' => 'nullable',
        'documento_dorso' => 'nullable',
    ));

    $fichaMedica = Auth::user()->fichaMedica;

    if ($request->file('archivo_medico')){
      $archivo = $request->file('archivo_medico');
      $path = $archivo->store('public/perfil');
      $oldPath = str_replace('storage', 'public', $fichaMedica->archivo_medico);
      if(Storage::exists($oldPath))
          Storage::delete($oldPath);

      $fichaMedica->archivo_medico = str_replace('public', 'storage', $path);
      $fichaMedica->save();
    }

    if ($request->file('documento_frente')){
      $archivo = $request->file('documento_frente');
      $path = $archivo->store('public/perfil');
      $oldPath = str_replace('storage', 'public', $fichaMedica->documento_frente);
      if(Storage::exists($oldPath))
          Storage::delete($oldPath);

      $fichaMedica->documento_frente = str_replace('public', 'storage', $path);
      $fichaMedica->save();
    }

    if ($request->file('documento_dorso')){
      $archivo = $request->file('documento_dorso');
      $path = $archivo->store('public/perfil');
      $oldPath = str_replace('storage', 'public', $fichaMedica->documento_dorso);
      if(Storage::exists($oldPath))
          Storage::delete($oldPath);

      $fichaMedica->documento_dorso = str_replace('public', 'storage', $path);
      $fichaMedica->save();
    }
  }
}




