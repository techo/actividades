<?php

namespace App\Http\Controllers\ajax;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Mail;
use App\Mail\VerificarMail;
use App\Persona;
use App\Actividad;
use App\VerificacionMailPersona;
use App\Rules\PassExiste;
use App\Inscripcion;

class UsuarioController extends Controller
{
  public function validar(Request $request, $verbo) {
    if(!($verbo == 'update' || $verbo == 'create')) abort(404);
      $rules = [];
      switch ($verbo) {
        case 'update':
          if($request->has('pass_actual')) $rules['pass_actual'] = ['required_with_all:pass,pass_confirmacion','min:8',new PassExiste()];
          if($request->has('pass')) $rules['pass'] = 'required_with_all:pass_actual,pass_confirmacion|min:8';
          if($request->has('pass_confirmacion')) $rules['pass_confirmacion'] = 'required_with_all:pass,pass_actual|same:pass';
        break;
        case 'create':
          if($request->has('email')) $rules['email'] = 'required|unique:Persona,mail,'.$request->id.',idPersona|email';
          if($request->has('pass')) $rules['pass'] = 'required|min:8';
        break;
      }
        if($request->has('nombre')) $rules['nombre'] = 'required';
        if($request->has('apellido')) $rules['apellido'] = 'required';
        if($request->has('nacimiento')) $rules['nacimiento'] = 'required|before:' . date('Y-m-d');
        if($request->has('telefono')) $rules['telefono'] = 'required|numeric';
        if($request->has('dni')) $rules['dni'] = 'required|regex:/^[A-Za-z]{0,2}[0-9]{7,8}[A-Za-z]{0,2}$/';
        $validatedData = $request->validate($rules);
        return ['success' => true, 'params' => array_keys($rules)];
    }

  public function create(Request $request) {
      $url = $request->session()->get('login_callback','');
      $this->validar($request,'create');
  	$persona = new Persona();
      $this->cargar_cambios($request, $persona);
      $persona->password = Hash::make($request->pass);
  	$persona->carrera = '';
  	$persona->anoEstudio = '';
  	$persona->idContactoCTCT = '';
  	$persona->statusCTCT = '';
  	$persona->lenguaje = '';
  	$persona->idRegionLT = 0;
  	$persona->idUnidadOrganizacional = 0;
  	$persona->idCiudad = 0;
      $persona->verificado = false;
      $persona->save();
      $verificacion = new VerificacionMailPersona();
      $verificacion->idPersona = $persona->idPersona;
      $verificacion->token = str_random(40);
      $verificacion->save();
      Auth::login($persona, true);
      $request->session()->regenerate();
      Mail::to($persona->mail)->send(new VerificarMail($persona));
      return ['login_callback' =>  $url, 'user' => $persona];
  }

  public function update(Request $request) {
      $this->validar($request,'update');
      $persona = Auth::user();        
      $this->cargar_cambios($request, $persona);
      if($request->has('pass')) {
          $persona->password = Hash::make($request->pass);
      }
      $persona->save();
      return ['user' => $persona->perfil()];
  }

  public function cargar_cambios($request,$persona) {
      $fechaNacimiento = new Carbon($request->nacimiento);
      $persona->apellidoPaterno = $request->apellido;
      $persona->dni = $request->dni;
      $persona->mail = $request->email;
      $persona->idLocalidad = $request->localidad;
      $persona->fechaNacimiento = $fechaNacimiento;
      $persona->nombres = $request->nombre;
      $persona->idPais = $request->pais;
      $persona->idPaisResidencia = $request->pais;
      $persona->idProvincia = $request->provincia;
      $persona->sexo = $request->sexo;
      $persona->telefonoMovil = $request->telefono;
      $persona->google_id = $request->google_id;
      $persona->facebook_id = $request->facebook_id;
      return $persona;
  }

  public function validar_nuevo_mail(Request $request) {
  	return Persona::where('mail', $request->email)->get()->count();
  }

  public function linkear(Request $request) {
      $url = $request->session()->get('login_callback','');
      $success = false;
      $persona = Persona::where('mail', $request->email)->first();
      if($persona) {
          $success = true;
          if($request->media == 'google') {
              $persona->google_id = $request->id;
          }
          if($request->media == 'facebook') {
              $persona->facebook_id = $request->id;
          }
      }
      if($success) {
          $persona->save();
          Auth::login($persona, true);
          $request->session()->regenerate();
      }
      return ['success' => $success, 'login_callback' => $url];
  } 

  public function perfil(Request $request)
  {
    $persona = Auth::user();
    $usuario = $persona->perfil();
    return $usuario;
  }    


    public function inscripciones(Request $request) {
        $inscripciones = Actividad::join('Inscripcion','Inscripcion.idActividad','=','Actividad.idActividad')->where('idPersona',Auth::user()->idPersona)->get();
        $data = [];
        foreach ($inscripciones as $inscripcion) {
            $data[] = [
                'nombreActividad' => $inscripcion->nombreActividad,
                'idActividad' => $inscripcion->idActividad
            ];
        }
        return $data;
    }

    public function desinscribir(Request $request, $idActividad) {
        $inscripciones = Inscripcion::where('idPersona',Auth::user()->idPersona)->where('idActividad', $idActividad)->get();
        foreach ($inscripciones as $inscripcion) {
            $inscripcion->delete();
        }
        return ['success' => true];
    }
}


