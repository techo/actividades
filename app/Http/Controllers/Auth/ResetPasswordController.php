<?php

namespace App\Http\Controllers\Auth;

//use Illuminate\Foundation\Auth\ResetsPasswords;
use App\Http\Controllers\Controller;
use App\Traits\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Si llegó acá desde un link protegido (ej. el de evaluación: login → "olvidé mi
     * contraseña"), volver a ese link y no a la home. La cookie la pone Handler::unauthenticated.
     */
    public function redirectTo()
    {
        $url = request()->cookie('after_login_url');
        if ($url && parse_url($url, PHP_URL_HOST) === request()->getHost()) {
            Cookie::queue(Cookie::forget('after_login_url'));
            return $url;
        }

        return '/';
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    private function credentials(Request $request)
    {
        return $request->only(
            'mail', 'password', 'password_confirmation', 'token'
        );
    }

}
