<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
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
    protected $redirectTo = '/home';

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

        if($authSuccess) {
            $request->session()->regenerate();
            return response(
                [
                    'success' => true,
                    'user' => Auth::user(),
                    'permisos' => Auth::user()->getAllPermissions()
                ],
                200
            );
        }

        return response(
            [
                'success' => false,
                'message' => 'El correo electrónico y/o la contraseña es incorrecta'
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
	if(url('/registro') != $request->headers->get('referer')) $request->session()->put('login_callback', $request->headers->get('referer'));
        if($provider == 'google') return Socialite::driver($provider)->redirect();
        return Socialite::driver($provider)->fields(['first_name', 'last_name', 'email', 'gender'])->redirect();
    }

    public function callbackFromProvider(Request $request, $provider) {
        $url = $request->session()->get('login_callback','');
        $personaData = new \stdClass();
        if($provider == 'google') {
            $user = Socialite::driver($provider)->stateless()->user();
            $personaData->nombre = $user->user['name']['givenName'];
            $personaData->apellido = $user->user['name']['familyName'];
            $personaData->email = $user->email;
            $personaData->google_id = $user->user['id'];
            $personaData->facebook_id = '';
            $personaData->password = bcrypt(str_random(30));
            $personaData->sexo = '';
        } else {
           $user = Socialite::driver($provider)->stateless()->fields([
                   'first_name', 'last_name', 'email', 'gender'
           ])->user();
            $personaData->nombre = $user->user['first_name'];
            $personaData->apellido = $user->user['last_name'];
            $personaData->email = $user->user['email'];
            $personaData->facebook_id = $user->user['id'];
            $personaData->google_id = '';
            $personaData->password = bcrypt(str_random(30));
            $personaData->sexo = $user->user['gender'] == 'male' ? 'M' : 'F';
        }
        $persona = Persona::where('mail',$personaData->email)->first();
        if(!$persona) {
            return view('registro')->with('persona', $personaData);
        } else {
            if($provider == 'google') {
                if($persona->google_id == $personaData->google_id) {
                    Auth::login($persona, true);
                    $request->session()->regenerate();
                } else {
                    return view('registro')->with('persona', $personaData)->with('linkear',true);    
                }
            }
            if($provider == 'facebook') {
                if($persona->facebook_id == $personaData->facebook_id) {
                    Auth::login($persona, true);
                    $request->session()->regenerate();
                }
            }
            if(Auth::check()) {
                $request->session()->forget('login_callback');
    		    if($url) return redirect($url);
	        }
        }
    }

}
