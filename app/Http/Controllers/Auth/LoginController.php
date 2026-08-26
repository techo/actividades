<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Socialite;
use App\Persona;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;


    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username()
    {
        return 'mail';
    }

    public function login(Request $request)
    {
        $credentials = $request->only($this->username(), 'password');
        $authSuccess = Auth::attempt($credentials, $request->has('remember'));
        $afterLoginUrl = '';
        if($authSuccess) {
            $request->session()->regenerate();

            if($request->hasCookie('after_login_url')){
                $afterLoginUrl = $request->cookie('after_login_url');
                Cookie::queue(Cookie::make('after_login_url', ''));
            }

            if(url('/registro') == $request->headers->get('referer')) {
                $afterLoginUrl = '/';
            }

            $request->session()->put('pais', Auth::user()->Pais->id);
            $request->session()->put('locale', Auth::user()->Pais->locale);

            return response(
                [
                    'success' => true,
                    'user' => Auth::user(),
                    'permisos' => Auth::user()->getAllPermissions(),
                    'after_login' => $afterLoginUrl,
                ],
                200
            );
        }

        return response(
            [
                'success' => false,
                'message' => __('frontend.login_error')
            ], 403
        );
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->invalidate();

        if($request->wantsJson()){
            return response(
                [
                    'success' => true,
                    'redirect_to' => '/'
                ],
                200
            );
        }

        return redirect('/');

    }

    public function redirectToProvider(Request $request, $provider)
    {
	if(url('/registro') != $request->headers->get('referer')) {
        if($request->hasCookie('after_login_url') && !empty($request->cookie('after_login_url'))){
            $afterLoginUrl = $request->cookie('after_login_url');
            Cookie::queue(Cookie::make('after_login_url', ''));
        } else {
            $afterLoginUrl = $request->headers->get('referer');
        }
        $request->session()->put('login_callback', $afterLoginUrl);
    }
    if($provider == 'google') return Socialite::driver($provider)->redirect();
    return Socialite::driver($provider)->fields(['first_name', 'last_name', 'email', 'gender'])->redirect();
    }

    public function callbackFromProvider(Request $request, $provider) {
        $url = $request->session()->get('login_callback','');
        $personaData = new \stdClass();
        if($provider == 'google') {
            // Sin stateless(): Socialite valida el parámetro OAuth `state` guardado en
            // sesión por redirectToProvider (protección CSRF del callback de login).
            try {
                $user = Socialite::driver($provider)->user();
            } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
                // El `state` OAuth no coincide (sesión perdida, botón atrás, reintento,
                // login abierto en otra pestaña). No es un error del sistema: en vez de
                // tirar 500 ("Whoops"), mandamos a reintentar el login.
                return redirect('/')->with('status', 'Tu sesión de ingreso expiró. Por favor, iniciá sesión nuevamente.');
            }
            // Google solo devuelve el email primario verificado; si explícitamente
            // viene sin verificar, no lo confiamos.
            $emailVerificado = $user->user['email_verified'] ?? $user->user['verified_email'] ?? true;
            if ($emailVerificado === false || $emailVerificado === 'false') {
                return view('registro')->with('persona', null)->with('mensaje', "El email de la cuenta de Google no está verificado.");
            }
            // Google (OpenID) devuelve given_name/family_name como OPCIONALES: cuentas sin
            // apellido (mononombre, cuentas de organización) los omiten. Coalescemos para no
            // romper el registro con "Undefined index"; el usuario completa lo que falte en el form.
            $personaData->nombre = $user->user['given_name'] ?? $user->name ?? '';
            $personaData->apellido = $user->user['family_name'] ?? '';
            $personaData->email = $user->email;
            $personaData->google_id = $user->user['id'] ?? '';
            $personaData->facebook_id = '';
            $personaData->genero = '';
        } else {
           try {
               $user = Socialite::driver($provider)->fields([
                       'first_name', 'last_name', 'email', 'gender'
               ])->user();
           } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
               // Ver nota en la rama de Google: state OAuth inválido → reintentar login.
               return redirect('/')->with('status', 'Tu sesión de ingreso expiró. Por favor, iniciá sesión nuevamente.');
           }
            $personaData->nombre = $user->user['first_name'];
            $personaData->apellido = $user->user['last_name'];
            $personaData->email = (array_key_exists('email', $user->user)) ? $user->user['email'] : null;
            $personaData->facebook_id = $user->user['id'];
            $personaData->google_id = '';

            if(isset($user->user['gender'])){
                $personaData->genero = $user->user['gender'] == 'male' ? 'M' : 'F';
            } else {
                $personaData->genero = '';
            }
        }
//        $personaData->password = bcrypt(str_random(30));
        $persona = Persona::where('mail',$personaData->email)->first();
        if(!$persona) {
            if($personaData->email == null)
                return view('registro')->with('persona', null)->with('mensaje', "La cuenta de facebook no tiene un email vinculado. Intente con otra red social o con usuario y contraseña");
            // Guardamos en sesión el email + id social verificados por el proveedor OAuth.
            // El registro (UsuarioController::create) usa SOLO estos valores para marcar
            // el email como verificado y asociar el id social; nunca los del request.
            $request->session()->put('registro_social', [
                'email'     => $personaData->email,
                'provider'  => $provider,
                'social_id' => $provider == 'google' ? $personaData->google_id : $personaData->facebook_id,
            ]);
            return view('registro')->with('persona', $personaData);
        } else {
            if($provider == 'google') {
                if($persona->google_id == $personaData->google_id) {
                    Auth::login($persona, true);
                    $request->session()->regenerate();
                } else {
                    // Guardamos en sesión los datos verificados por el proveedor OAuth.
                    // El endpoint `linkear` usa SOLO estos valores, nunca los del request,
                    // para evitar el linkeo/login a partir de un email arbitrario.
                    $request->session()->put('link_social', [
                        'email'     => $personaData->email,
                        'provider'  => 'google',
                        'social_id' => $personaData->google_id,
                    ]);
                    return view('registro')->with('persona', $personaData)->with('linkear',true);
                }
            }
            if($provider == 'facebook') {
                if($persona->facebook_id == $personaData->facebook_id) {
                    Auth::login($persona, true);
                    $request->session()->regenerate();
                } else {
                    $request->session()->put('link_social', [
                        'email'     => $personaData->email,
                        'provider'  => 'facebook',
                        'social_id' => $personaData->facebook_id,
                    ]);
                    return view('registro')->with('persona', $personaData)->with('linkear',true);
                }
            }
            if(Auth::check()) {
                $request->session()->forget('login_callback');
    		    if($url) return redirect($url);
	        }
        }
    }

}
