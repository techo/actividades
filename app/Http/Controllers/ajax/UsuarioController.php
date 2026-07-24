<?php

namespace App\Http\Controllers\ajax;

use App\Events\RegistroUsuario;
use App\GrupoRolPersona;
use App\Http\Controllers\BaseController;
use App\Http\Resources\CoordinadorResource;
use App\Http\Resources\MisActividadesResource;
use App\Http\Resources\PerfilResource;
use App\Inscripcion;
use App\Pais;
use App\Persona;
use App\Rules\PassExiste;
use App\Search\CoordinadoresSearch;
use App\Search\MisActividadesSearch;
use App\Services\ImageUploadService;
use App\VerificacionMailPersona;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
        if($request->has('genero')) $rules['genero'] = 'nullable';
        if($request->has('pais')) $rules['pais'] = 'required|exists:atl_pais,id';
        if($request->has('provincia')) $rules['provincia'] = 'nullable|exists:atl_provincias,id';
        if($request->has('localidad')) $rules['localidad'] = 'nullable|exists:atl_localidades,id';
        if($request->has('nacimiento')) $rules['nacimiento'] = 'nullable|date|before:' . date('Y-m-d') . '|after:' . Carbon::now()->subYears(85)->format('Y-m-d');
        if($request->has('telefono')) $rules['telefono'] = 'required|regex:/^\+\d{1,3}\d{7,15}$/';
        if($request->has('dni')) $rules['dni'] = 'nullable';
        $validatedData = $request->validate($rules);
        return ['success' => true, 'params' => array_keys($rules)];
    }

  /**
   * Devuelve las credenciales sociales VERIFICADAS del registro, o null.
   *
   * Nunca se confía en un id social suelto del request (permitía crear cuentas
   * pre-verificadas para emails ajenos y obtener token). Las fuentes válidas son:
   *  - Web: la sesión `registro_social`, seteada por LoginController tras el flujo OAuth.
   *  - Mobile: un `token` de proveedor validado server-side contra el proveedor real.
   *
   * @return array{provider:string,social_id:string,email:string}|null
   */
  private function socialVerificado(Request $request)
  {
      $sesion = $request->session()->get('registro_social');
      if (is_array($sesion) && !empty($sesion['social_id']) && !empty($sesion['email'])) {
          return $sesion;
      }

      if ($request->filled('provider') && $request->filled('token')) {
          try {
              $data = \App\Services\SocialAuth\SocialProviderFactory::make($request->provider)
                  ->validate($request->token);
          } catch (\Exception $e) {
              return null;
          }
          if ($data && !empty($data['email']) && !empty($data['email_verified']) && !empty($data['social_id'])) {
              return [
                  'provider'  => $data['provider'],
                  'social_id' => $data['social_id'],
                  'email'     => $data['email'],
              ];
          }
      }

      return null;
  }

  /**
   * Aplica sobre la persona las credenciales sociales verificadas (o las limpia).
   * Solo aquí se setean google_id/facebook_id/apple_id, el email verificado y
   * la marca email_verified_at.
   */
  private function aplicarSocialVerificado(Persona $persona, $social)
  {
      $persona->google_id   = null;
      $persona->facebook_id = null;
      $persona->apple_id    = null;

      if ($social) {
          $columna = $social['provider'] . '_id';
          $persona->{$columna} = $social['social_id'];
          // El email debe ser el verificado por el proveedor, no el del request.
          $persona->mail = $social['email'];
          $persona->email_verified_at = now();
      }
  }

  public function create(Request $request) {
      $url = $request->session()->get('login_callback','');
      $this->validar($request,'create');
      $persona = new Persona();
      $this->cargar_cambios($request, $persona);

      $social = $this->socialVerificado($request);
      $this->aplicarSocialVerificado($persona, $social);

      $persona->password = $social ? Hash::make(str_random(30)) : Hash::make($request->pass);
      $persona->idUnidadOrganizacional = 0;
      $persona->recibirMails = 1;
      $persona->unsubscribe_token = Uuid::generate()->string;
      $persona->save();

      if (!$social) {
        $persona->sendRegistroUsuarioNotification();
      }
      $request->session()->forget('registro_social');

      $pais = Pais::find($persona->idPais);
      $request->session()->regenerate();
      Auth::login($persona, true);
      $request->session()->flash('mensaje', __('messages.account_created'));

      return ['login_callback' =>  $url, 'user' => null, 'abreviacionPais' => $pais->abreviacion, 'loginSocial' => (bool) $social];
  }

  public function apiCreate(Request $request) {
        $this->validar($request, 'create');

        $persona = new Persona();
        $this->cargar_cambios($request, $persona);

        $social = $this->socialVerificado($request);
        $this->aplicarSocialVerificado($persona, $social);

        $persona->password = $social ? Hash::make(str_random(30)) : Hash::make($request->pass);
        $persona->idUnidadOrganizacional = 0;
        $persona->recibirMails = 1;
        $persona->unsubscribe_token = Uuid::generate()->string;
        $persona->save();

        if (!$social) {
            $persona->notify(new \App\Notifications\RegistroUsuario);
        }

        // 🔑 En API devolvés un token en lugar de usar Auth::login()
        $token = $persona->createToken('Token Name')->accessToken;

        $pais = Pais::find($persona->idPais);

        return response()->json([
            'mensaje' => __('messages.account_created'),
            'token' => $token,
            'persona' => $persona,
            'abreviacionPais' => $pais->abreviacion,
            'loginSocial' => (bool) $social,
        ]);
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
      $persona->genero = $request->genero;
      $persona->telefonoMovil = $request->telefono;
      // Los ids sociales NO se toman del request (no son confiables): se setean solo
      // desde una fuente verificada (sesión OAuth en web / token validado en mobile),
      // ver aplicarSocialVerificado().
      $persona->acepta_marketing = $request->acepta_marketing;
      $persona->canal_contacto = $request->canal_contacto;
      $persona->estadoPersona = $request->estadoPersona;
      $persona->instagram = $request->instagram;

      return $persona;
  }

  public function cambiar_photo(Request $request)
	{
		$persona = Auth::user();
		$this->validate($request, array(
			'photo' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:10240',
		));

		if ($request->file('photo')){
			$archivo = $request->file('photo');
			$path = ImageUploadService::store($archivo, 'public/perfil/img');
			$oldPath = str_replace('storage', 'public', $persona->photo);
			if(Storage::exists($oldPath))
				Storage::delete($oldPath);

			$persona->photo = str_replace('public', 'storage', $path);
			$success = $persona->save();

      return response(
        [
            'success' => $success,
            'newUrl' => $persona->photo,
            'mensaje' => "Imagen subida OK"
        ],
        200
    );
		}
  
	}
  public function validar_nuevo_mail(Request $request) {
  	return Persona::where('mail', $request->email)->get()->count();
  }

  public function linkear(Request $request) {
      $url = $request->session()->get('login_callback','');
      $success = false;

      // El email y el id social provienen EXCLUSIVAMENTE de la sesión, seteados por
      // LoginController::callbackFromProvider tras validar el flujo OAuth con el proveedor.
      // Nunca se confía en el request: de lo contrario cualquiera podría loguearse como
      // otro usuario enviando un email arbitrario (account takeover no autenticado).
      $link = $request->session()->get('link_social');

      if(is_array($link) && !empty($link['email']) && !empty($link['social_id'])) {
          $persona = Persona::where('mail', $link['email'])->first();
          if($persona) {
              $success = true;
              if($link['provider'] == 'google') {
                  $persona->google_id = $link['social_id'];
              }
              if($link['provider'] == 'facebook') {
                  $persona->facebook_id = $link['social_id'];
              }
              $persona->save();
              Auth::login($persona, true);
              $request->session()->regenerate();
              $request->session()->forget('link_social');
          }
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
        if ($request->is('api/*')) {
            return $resourceCollection;
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
          // Parámetro bindeado (?): no concatenar input en SQL.
          $query->whereRaw("concat(' ', nombres, ' ', apellidoPaterno, ' ', mail, ' ', dni) like ?", ['%' . $palabra . '%']);
        }

        // Aislamiento por país: solo personas del país permitido del usuario autenticado.
        $query->where('idPais', auth()->user()->idPaisPermitido);

        $query->take(25)->orderBy('nombres', 'asc');

        // Devolvemos un Resource con campos acotados, nunca el modelo Eloquent crudo.
        return CoordinadorResource::collection($query->get());
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

        if ($request->expectsJson() || $request->is('api/*')) {
          $request->user()->token()->revoke();
          return response()->json(['success' => true, 'message' => 'Usuario eliminado correctamente']);
        } else {
          return app('App\Http\Controllers\Auth\LoginController')->logout($request); //Todo: Mejorar pasandolo a un servicio
        }
    }
}

