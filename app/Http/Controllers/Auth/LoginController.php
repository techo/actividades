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
                    'user' => Auth::user()
                ],
                200
            );
        }

        return response(
            [
                'success' => false,
                'message' => 'Las credenciales no coinciden'
            ], 403
        );
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->invalidate();
        return response(
            [
                'success' => true,
                'redirect_to' => '/'
            ],
            200
        );
    }

    public function redirectToProvider($provider)
    {
	if($provider == 'google') return Socialite::driver($provider)->redirect();
        return Socialite::driver($provider)->fields(['first_name', 'last_name', 'email', 'gender'])
	->redirect();
    }

    public function callbackFromProvider($provider) {
#	if($provider == 'google') return dd(Socialite::driver($provider)->user());
        $persona = new \stdClass();
	if($provider == 'google') {
        	$user = Socialite::driver($provider)->stateless()->user();
        	$persona->nombre = $user->user['name']['givenName'];
        	$persona->apellido = $user->user['name']['familyName'];
        	$persona->email = $user->email;
        	$persona->google_id = $user->user['id'];
        	$persona->facebook_id = '';
	} else {
	        $user = Socialite::driver($provider)->stateless()->fields([
        	        'first_name', 'last_name', 'email', 'gender'
	        ])->user();
         	$persona->nombre = $user->user['first_name'];
        	$persona->apellido = $user->user['last_name'];
        	$persona->email = $user->user['email'];
        	$persona->facebook_id = $user->user['id'];
        	$persona->google_id = '';
	}
        $persona->sexo = $user->user['gender'] == 'male' ? 'M' : 'F';
        return view('registro')->with('persona', $persona);
    } 
}
