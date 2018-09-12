<?php

namespace App\Http\Controllers\ajax;

use App\Http\Resources\PerfilResource;
use App\Search\CoordinadoresSearch;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Persona;
use App\VerificacionMailPersona;
use App\Http\Resources\CoordinadorResource;
use App\Http\Resources\MisActividadesResource;
use App\Rules\PassExiste;
use App\Inscripcion;
use App\Search\MisActividadesSearch;
use Webpatser\Uuid\Uuid;

class UsuarioController extends BaseController
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
          if($request->has('pass') && !$request->google_id && !$request->facebook_id) $rules['pass'] = 'required|min:8';
          if($request->has('privacidad')) $rules['privacidad'] = 'required';
        break;
      }
        if($request->has('nombre')) $rules['nombre'] = 'required';
        if($request->has('apellido')) $rules['apellido'] = 'required';
        if($request->has('sexo')) $rules['sexo'] = 'required';
        if($request->has('nacimiento')) $rules['nacimiento'] = 'required|date|before:' . date('Y-m-d');
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
      $persona->password = (!empty($request->google_id) || !empty($request->facebook_id)) ? Hash::make(str_random(30)) : Hash::make($request->pass);
      $persona->carrera = '';
      $persona->anoEstudio = '';
      $persona->idContactoCTCT = '';
      $persona->statusCTCT = '';
      $persona->lenguaje = '';
      $persona->idRegionLT = 0;
      $persona->idUnidadOrganizacional = 0;
      $persona->idCiudad = 0;
      $persona->verificado = false;
      $persona->recibirMails = 1;
      $persona->unsubscribe_token = Uuid::generate()->string;
      $persona->save();
      $verificacion = new VerificacionMailPersona();
      $verificacion->idPersona = $persona->idPersona;
      $verificacion->token = str_random(40);
      $verificacion->save();
      Auth::login($persona, true);
      $request->session()->regenerate();
      return ['login_callback' =>  $url, 'user' => $persona];
  }

  public function update(Request $request) {
      $this->validar($request,'update');
      $persona = Auth::user();
      $this->cargar_cambios($request, $persona);
      $persona->recibirMails = (int) $request->recibirMails;
      if($request->has('pass')) {
          $persona->password = Hash::make($request->pass);
      }
      $persona->save();
      return ['user' => new PerfilResource($persona)];
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
      $persona->acepta_marketing = $request->acepta_marketing;
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

  public function perfil()
  {
    $persona = Auth::user();
    $usuario = new PerfilResource($persona);
    return $usuario;
  }


  public function inscripciones(Request $request, $items=10) {

    $inscripciones = MisActividadesSearch::apply($request);
    $resourceCollection = [
        'data' => []
    ];
    if ($inscripciones->count() > 0) {
        $resourceCollection = [];
        foreach ($inscripciones as $inscripcion) {
            $resourceCollection[] = new MisActividadesResource($inscripcion);
        }
        return $this->paginate($resourceCollection, $items, $request->query());
    }
    return $resourceCollection;
  }

    public function desinscribir(Request $request, $idActividad) {
        $inscripciones = Inscripcion::where('idPersona', Auth::user()->idPersona)->where('idActividad', $idActividad)->get();
        foreach ($inscripciones as $inscripcion) {
          $inscripcion->estado = 'Desinscripto';
          $inscripcion->save();
        }
        return ['success' => true];
    }

    public function getCoordinadores(Request $request)
    {
        // Esto debería filtrar por rol
        $result = CoordinadoresSearch::apply($request);
        $coordinadores = CoordinadorResource::collection($result);
        return $coordinadores;
    }

    public function delete(Request $request)
    {
        // Traer todas las inscripciones de actividades futuras del usuario

        $persona = Persona::find(auth()->user()->idPersona);

        $inscripcionesFuturas = $persona->inscripciones()
            ->join('Actividad', 'Inscripcion.idActividad', '=', 'Actividad.idActividad')
            ->whereNotIn('estado',['Desinscripto'])
            ->whereDate('Actividad.fechaInicio', '>=', Carbon::now())
            ->get();

        // actualizar las inscripciones como desinscripto
        foreach ($inscripcionesFuturas as $inscripcion){
            $inscripcion = Inscripcion::find($inscripcion->idInscripcion);
            $inscripcion->estado = 'Desinscripto';
            $inscripcion->save();
        }

        // ofuscar en tabla persona
        $persona->nombres = str_random(30);
        $persona->apellidoPaterno = str_random(30);
        $persona->telefono = str_random(30);
        $persona->telefonoMovil = str_random(30);
        $persona->dni = str_random(8);
        $persona->mail = str_random(40);
        $persona->recibirMails = 0;
        $persona->acepta_marketing = 0;

        // grabar
        $persona->save();
        $request->session()->invalidate();
        return response()->json('Usuario eliminado correctamente', 200);
    }
}

