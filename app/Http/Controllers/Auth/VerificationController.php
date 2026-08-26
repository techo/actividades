<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Persona;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
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
        // 'verify' NO exige login: el link llega por mail y se abre en un navegador
        // sin sesión. Su seguridad es la firma de la URL ('signed'), no el auth.
        $this->middleware('auth')->except('verify');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    /**
     * Verifica el email a partir del link firmado del mail, sin requerir sesión.
     *
     * El link es un temporarySignedRoute con el id de la persona (válido 24h):
     * el middleware 'signed' garantiza que el id no fue manipulado y que no expiró,
     * así que verificamos por id en vez de depender del usuario logueado (antes el
     * 'auth' del constructor redirigía al login y la verificación no corría cuando
     * el link se abría en un navegador sin sesión).
     */
    public function verify(Request $request)
    {
        $persona = Persona::findOrFail($request->route('id'));

        if (! $persona->hasVerifiedEmail()) {
            $persona->markEmailAsVerified();
            event(new Verified($persona));
        }

        // Si quien abre el link ya está logueado como esa misma persona (verificación
        // desde la web), respetamos su destino previsto y seguimos el flujo web.
        if ($request->user() && $request->user()->getKey() == $persona->getKey()) {
            return redirect($this->redirectPath())->with('verified', true);
        }

        // Caso típico: el link se abrió en el navegador y el registro venía de la app
        // móvil. Mostramos una página de éxito que intenta reabrir MiTECHO por deep
        // link para que la app refresque el estado a "verificado".
        //
        // NOTA (app móvil, OTRO repo): la app debe manejar este deep link y, al
        // recibirlo, re-consultar su persona (el back ya dejó email_verified_at seteado)
        // para actualizar la UI. Ajustar el esquema/host si la app espera otro.
        return response()->view('auth.email-verified', [
            'appDeepLink' => 'mitecho://email-verificado?persona=' . $persona->getKey(),
        ]);
    }

    protected function redirectTo()
    {
        if(session()->get("url.intended")){
            return session()->get("url.intended");
        }
        return $this->redirectTo;
    }
}
