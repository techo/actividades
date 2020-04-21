<?php

namespace App\Http\Controllers\ajax;

use App\Events\RegistroUsuario;
use App\GrupoRolPersona;
use App\Http\Controllers\BaseController;
use App\Http\Resources\CoordinadorResource;
use App\Http\Resources\MisActividadesResource;
use App\Http\Resources\PerfilResource;
use App\Inscripcion;
use App\Persona;
use App\Rules\PassExiste;
use App\Search\CoordinadoresSearch;
use App\Search\MisActividadesSearch;
use App\VerificacionMailPersona;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
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
          if($request->has('email')) $rules['email'] = 'required|unique:Persona,mail,'.$request->id.',idPersona|email';
        break;
        case 'create':
          if($request->has('email')) $rules['email'] = 'required|unique:Persona,mail,'.$request->id.',idPersona,deleted_at,NULL|email';
          if($request->has('pass') && !$request->google_id && !$request->facebook_id) $rules['pass'] = 'required|min:8';
          if($request->has('privacidad')) $rules['privacidad'] = 'accepted';
        break;
      }
        if($request->has('nombre')) $rules['nombre'] = 'required';
        if($request->has('apellido')) $rules['apellido'] = 'required';
        if($request->has('sexo')) $rules['sexo'] = 'required';
        if($request->has('pais')) $rules['pais'] = 'required|exists:atl_pais,id';
        if($request->has('nacimiento')) $rules['nacimiento'] = 'required|date|before:' . date('Y-m-d');
        if($request->has('telefono')) $rules['telefono'] = 'required|numeric';
        // if($request->has('dni')) $rules['dni'] = 'required|regex:/^[A-Za-z0-9]{6,10}$/';
        $validatedData = $request->validate($rules);
        return ['success' => true, 'params' => array_keys($rules)];
    }

  public function create(Request $request) {
      $url = $request->session()->get('login_callback','');
      $this->validar($request,'create');
      $persona = new Persona();
      $this->cargar_cambios($request, $persona);
      $persona->password = (!empty($request->google_id) || !empty($request->facebook_id)) ? Hash::make(str_random(30)) : Hash::make($request->pass);
      $persona->idUnidadOrganizacional = 0;
      $persona->recibirMails = 1;
      $persona->unsubscribe_token = Uuid::generate()->string;
      $persona->save();

      event(new RegistroUsuario($persona));

      $request->session()->regenerate();
      $request->session()->flash('mensaje', __('messages.account_created'));

      return ['login_callback' =>  '/', 'user' => null];
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
        
        Inscripcion::where('idPersona', Auth::user()->idPersona)
          ->where('idActividad', $idActividad)
          ->get()
          ->each(function($inscripcion) {
            $inscripcion->delete();
          });

        return ['success' => true];
    }

    public function getCoordinadores(Request $request)
    {
        // Esto debería filtrar por rol
        $result = CoordinadoresSearch::apply($request);
        $coordinadores = CoordinadorResource::collection($result);
        return $coordinadores;
    }

    public function getPersonas(Request $request)
    {
        $query = (new Persona)->newQuery();
        
        $palabras = explode(' ', $request->q);

        foreach ($palabras as $palabra) {
          $query->whereRaw("concat(' ', nombres, ' ', apellidoPaterno, ' ', mail, ' ', dni) like '%" . $palabra . "%'");
        }

        $query->take(25)->orderBy('nombres', 'asc');

        return $query->get();
    }

    public function delete(Request $request)
    {
        // Traer todas las inscripciones de actividades futuras del usuario

        $persona = Persona::find(auth()->user()->idPersona);

        $inscripcionesFuturas = \App\Inscripcion::whereHas('Actividad', function ($query) {
            $query->whereDate('fechaInicio', '>=', Carbon::now())
              ->where('idPersona', '=', auth()->user()->idPersona);
        })->get();

        foreach ($inscripcionesFuturas as $inscripcion) {
          $inscripcion->delete();
        }

        // ofuscar en tabla persona
        $persona->nombres = 'Usuario eliminado';
        $persona->apellidoPaterno = '';
        $persona->telefono = str_random(30);
        $persona->telefonoMovil = str_random(30);
        $persona->dni = str_random(8);
        $persona->mail = str_random(40);
        $persona->recibirMails = 0;
        $persona->acepta_marketing = 0;

        // grabar
        $persona->save();
        return app('App\Http\Controllers\Auth\LoginController')->logout($request); //Todo: Mejorar pasandolo a un servicio
    }
}

