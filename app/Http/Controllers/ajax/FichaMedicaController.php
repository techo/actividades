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

      Log::info("hoals");
      $request->validate([
              'contacto_nombre' => 'nullable',
              'contacto_telefono' => 'nullable',
              'contacto_relacion' => 'nullable',
              'grupo_sanguinieo' => 'nullable',
              'cobertura_nombre' => 'nullable',
              'cobertura_numero' => 'nullable',
              'confirma_datos' => 'nullable',

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
      $fichaMedica->cobertura_nombre = $request->cobertura_nombre;
      $fichaMedica->cobertura_numero = $request->cobertura_numero;
      $fichaMedica->confirma_datos = $request->confirma_datos;

      $fichaMedica->save();
  }

  public function uploadArchivoMedico(Request $request)
  {
    $this->validate($request, array(
        'archivo_medico' => 'required',
    ));
    $archivoMedico = $request->file('archivo_medico');
    $fichaMedica = Auth::user()->fichaMedica;
    if($archivoMedico){
      $path = $request->file('archivo_medico')->store('public/perfil');
      $oldPath = str_replace('storage', 'public', $fichaMedica->archivo_medico);
      if(Storage::exists($oldPath))
          Storage::delete($oldPath);

      $fichaMedica->archivo_medico = str_replace('public', 'storage', $path);
      $fichaMedica->save();
      
    }
}

  }



