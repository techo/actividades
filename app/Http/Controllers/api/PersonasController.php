<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CrearPersona;
use App\Persona;
use App\Inscripcion;
use App\Pais;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Webpatser\Uuid\Uuid;
use App\Services\SocialAuth\SocialProviderFactory;
use Illuminate\Support\Facades\Log;

class PersonasController extends Controller
{ 
    public function index()
    {
        return Persona::all();
    }

    public function show(Persona $persona)
    {
        $this->autorizarPersonaPropia($persona);

        return $persona;
    }

    public function getInscripciones(Request $request)
    {
        $Inscripciones = Inscripcion::with('actividad')
                                ->where('idPersona', auth('api')->user()->idPersona)
                                ->where('presente', '1')
                                ->get();

        return response(
                [
                    'success' => true,
                    'inscripciones' => $Inscripciones,
                    'mensaje' => "Inscripciones OK"
                ],
                200
            );
    }

    public function getPersonaxMail($mail)
    {
        $persona = Persona::where('mail', $mail)->get();
        return $persona;
    }

    public function login(request $request)
    {
        $credentials = $request->only('mail', 'password');
        $authSuccess = Auth::attempt($credentials, $request->has('remember'));
        $afterLoginUrl = '';

        if ($authSuccess){
            $user = Persona::where('mail', $credentials['mail'])->first();
            $token = $user->createToken('Token Name')->accessToken;

            if (is_null($user->primer_acceso_app)) {
                $user->primer_acceso_app = now();
            }
            $user->ultimo_acceso_app = now();
            $user->save();

            return response(
                [
                    'success' => true,
                    'user' => Auth::user(),
                    'mensaje' => "Autenticación OK",
                    'token' => $token,
                ],
                200
            );
        } else {
            return response(
                [
                    'success' => false,
                    'user' => Auth::user(),
                    'mensaje' => "Error de autenticación",
                ],
                401
            );
        }
    }

    public function providerLogin(Request $request)
    {
        $request->validate([
            'provider' => 'required|string',
            'token'    => 'required|string',
        ]);

        try {
            $provider = SocialProviderFactory::make($request->provider);
        } catch (\Exception $e) {
            return response(['success' => false, 'mensaje' => 'Proveedor inválido'], 400);
        }

        $data = $provider->validate($request->token);

        if (!$data || empty($data['email'])) {
            return response(['success' => false, 'mensaje' => 'Token inválido'], 401);
        }

        if (!$data['email_verified']) {
            return response(['success' => false, 'mensaje' => 'Email no verificado'], 403);
        }

        $persona = Persona::where('mail', $data['email'])->first();

        if (!$persona) {
            return response(['success' => false, 'mensaje' => 'Usuario no encontrado'], 404);
        }

        $column = $data['provider'] . '_id';

        if ($persona->$column && $persona->$column !== $data['social_id']) {
            Log::warning('Intento takeover cuenta social', [
                'persona_id' => $persona->id,
                'provider'   => $data['provider'],
            ]);

            return response(['success' => false, 'mensaje' => 'Cuenta ya asociada'], 409);
        }

        $persona->$column = $data['social_id'];
        $persona->email_verified_at = now();
        if (is_null($persona->primer_acceso_app)) {
            $persona->primer_acceso_app = now();
        }
        $persona->ultimo_acceso_app = now();
        $persona->save();

        $token = $persona->createToken('social-login')->accessToken;
        $pais = Pais::find($persona->idPais);

        return response([
            'success'           => true,
            'persona'           => $persona,
            'token'             => $token,
            'abreviacionPais'   => optional($pais)->abreviacion,
            'loginSocial'       => true,
        ]);
    }

    public function ping(Request $request)
    {
        $persona = auth('api')->user();
        $persona->ultimo_acceso_app = now();
        $persona->save();

        return response(['success' => true], 200);
    }

    public function logout(request $request)
    {   
        $user = Auth::user()->token();
        $user->revoke();
        
        return response(
                [
                    'success' => true,
                    'mensaje' => "Sesión Cerrada",
                ],
                200
        );
    }

    public function create(Request $request) {
        $this->validar($request, 'create');

        $persona = new Persona();
        $this->cargar_cambios($request, $persona);
        $persona->password = (!empty($request->google_id) || !empty($request->facebook_id)) 
            ? Hash::make(str_random(30)) 
            : Hash::make($request->pass);

        $persona->idUnidadOrganizacional = 0;
        $persona->recibirMails = 1;
        $persona->unsubscribe_token = Uuid::generate()->string;

        if (!empty($request->google_id) || !empty($request->facebook_id)){
            $persona->email_verified_at = now();
        }
        $persona->save();
        if (empty($request->google_id) && empty($request->facebook_id)){
            $persona->sendRegistroUsuarioNotification();
        }

        // 🔑 En API devolvés un token en lugar de usar Auth::login()
        $token = $persona->createToken('Token Name')->accessToken;

        $pais = Pais::find($persona->idPais);

        return response()->json([
            'mensaje' => __('messages.account_created'),
            'token' => $token,
            'persona' => $persona,
            'abreviacionPais' => $pais->abreviacion,
            'loginSocial' => (!empty($request->google_id) || !empty($request->facebook_id)),
        ]);
    }
    public function register(CrearPersona $request)
    {   
        $fields = $request->validated();

        $persona = Persona::create([
            'dni' => $fields['dni'],
            'nombres' => $fields['nombres'],
            'apellidoPaterno' => $fields['apellidoPaterno'],
            'mail' => $fields['mail'],
            'password' => bcrypt($fields['password']),
            'fechaNacimiento' => $fields['fechaNacimiento'],
            'telefono' => $fields['telefono'],
            'telefonoMovil' => $fields['telefonoMovil'],
            'recibirMails' => $fields['recibirMails'],
            'acepta_marketing' => $fields['acepta_marketing'],
            'idPais' => $fields['idPais'],
            'idProvincia' => $fields['idProvincia'],
            'idLocalidad' => $fields['idLocalidad'],
            'idUnidadOrganizacional' => $fields['idUnidadOrganizacional'],
            'unsubscribe_token' => (string) Uuid::generate(),
        ]);

        $persona->sendEmailVerificationNotification();
        $token = $persona->createToken('Token Name')->accessToken;

        return response(
                [
                    'success' => true,
                    'persona' => $persona,
                    'mensaje' => "Persona Creada, validacion de mail pendiente",
                    'token'   => $token,
                ],
                201
            );
    }

  


    public function update(Request $request, Persona $persona)
    {
        $this->autorizarPersonaPropia($persona);

        $fields = $this->validate($request, [
            'mail' => 'required',
            'nombres' => 'required',
            'apellidoPaterno' => 'required',
            'fechaNacimiento' => 'required|date',
            'telefono' => 'required|integer',
            'genero' => 'required',
            'instagram' => 'required',
            'telefonoMovil' => 'required|integer',
            'dni' => 'required|integer',
            'recibirMails' => 'required|boolean',
            'acepta_marketing' => 'required|boolean',
            'idPais' => 'required|integer',
            'idProvincia' => 'required|integer',
            'idLocalidad' => 'required|integer',
            'idUnidadOrganizacional' => 'required|integer',
        ]);

        $persona->update([
            'dni' => $fields['dni'],
            'nombres' => $fields['nombres'],
            'apellidoPaterno' => $fields['apellidoPaterno'],
            'mail' => $fields['mail'],
            'fechaNacimiento' => $fields['fechaNacimiento'],
            'telefono' => $fields['telefono'],
            'genero' => $fields['genero'],
            'instagram' => $fields['instagram'],
            'telefonoMovil' => $fields['telefonoMovil'],
            'recibirMails' => $fields['recibirMails'],
            'acepta_marketing' => $fields['acepta_marketing'],
            'idPais' => $fields['idPais'],
            'idProvincia' => $fields['idProvincia'],
            'idLocalidad' => $fields['idLocalidad'],
            'idUnidadOrganizacional' => $fields['idUnidadOrganizacional'],
        ]);

        return response(
                [
                    'success' => true,
                    'persona' => $persona,
                    'mensaje' => "Persona Actualizada",
                ],
                200
            );
    }

    public function delete(Persona $persona)
    {
        $persona->delete();

        return response()->json(null, 204);
    }

    /**
     * La API mobile solo opera sobre el perfil del usuario autenticado.
     * El backoffice usa ajax\PersonasController (middleware admin), no este controlador.
     */
    private function autorizarPersonaPropia(Persona $persona)
    {
        if (auth('api')->user()->idPersona !== $persona->idPersona) {
            abort(403, 'No autorizado para operar sobre otra persona.');
        }
    }
}
