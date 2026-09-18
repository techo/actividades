<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Persona;
use App\Traits\SendsPasswordResetEmails;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    // Se aliasa el método del trait para poder envolverlo (restaurar antes de enviar).
    use SendsPasswordResetEmails {
        sendResetLinkEmail as baseSendResetLinkEmail;
    }

    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Envía el link de reset, recuperando primero cuentas dadas de baja.
     *
     * El broker de reset excluye a los borrados (scope de SoftDeletes), así que
     * "olvidé mi contraseña" no los encontraba y quedaban encerrados. Si no hay una
     * cuenta ACTIVA con ese mail pero existe una borrada, la restauramos para que el
     * flujo pueda enviarle el link. El acceso real igual exige el token que llega al
     * correo (solo el dueño del buzón lo recibe). Decisión de producto: se prioriza la
     * recuperación; un admin siempre puede volver a darla de baja.
     */
    public function sendResetLinkEmail(Request $request)
    {
        if ($request->filled('mail') && !Persona::where('mail', $request->mail)->exists()) {
            $borrada = Persona::onlyTrashed()->where('mail', $request->mail)->first();
            if ($borrada) {
                $borrada->restore();
            }
        }

        return $this->baseSendResetLinkEmail($request);
    }
}
