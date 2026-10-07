<?php

namespace App\Http\Controllers;

use App\Inscripcion;
use App\Mail\MailInscripcionConfirmada;
use App\Mail\MailInscripcionPagoFueraDeFecha;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PagosController extends Controller
{
    public function response(Request $request, $idInscripcion)
    {
        $inscripcion = Inscripcion::where('idInscripcion', $idInscripcion)
            ->with(['punto_encuentro'])
            ->first();

        $config = json_decode($inscripcion->actividad->pais->config_pago);
        $paymentClass = 'App\\Payments\\' . $config->payment_class;
        $payment = new $paymentClass($inscripcion);
        $payment->setRequest($request);

        $fecha_transaccion = null;
        if($request->filled('processingDate'))
            $fecha_transaccion = Carbon::parse($request->processingDate);

        // Fecha límite inclusiva del día (ver Actividad::pagoDentroDeFecha). Antes
        // usaba lessThan (exclusivo) contra fechaLimitePago a las 00:00, así que un
        // pago hecho el propio día límite caía como "fuera de fecha". confirmation()
        // ya usaba <= (inclusivo): ahora ambos comparten el mismo criterio. El corto
        // circuito por !fechaLimitePago se mantiene para no exigir la fecha de la
        // transacción cuando la actividad no tiene plazo.
        if ($payment->success() && (
                !$inscripcion->actividad->fechaLimitePago ||
                $inscripcion->actividad->pagoDentroDeFecha($fecha_transaccion)
            )) {
            return view('inscripciones.pagada', ['inscripcion' => $inscripcion, 'actividad' => $payment->actividad]);
        }
        elseif ($payment->success()) {
            return view('pagos.fuera_de_fecha', ['inscripcion' => $inscripcion, 'actividad' => $payment->actividad]);
        }

        return view('pagos.response')->with('payment', $payment);

    }

    public function confirmation(Request $request, $idInscripcion)
    {
        $inscripcion = Inscripcion::findOrFail($idInscripcion);
        $config = json_decode($inscripcion->actividad->pais->config_pago);
        $paymentClass = 'App\\Payments\\' . $config->payment_class;
        $payment = new $paymentClass($inscripcion);
        $payment->setRequest($request);

        // La ruta de confirmación es un webhook público (sin auth, exento de
        // CSRF): la única garantía de que la notificación viene realmente de
        // la pasarela de pago y no de un tercero es esta verificación de
        // firma. Sin esto, cualquiera podía marcar una inscripción como
        // pagada forjando el POST. Ver docs/master-plan-estabilizacion.md.
        if (!$payment->verifyConfirmationSignature()) {
            \Log::warning('Confirmación de pago rechazada: firma inválida o no correspondiente a la inscripción', [
                'idInscripcion' => $idInscripcion,
                'ip' => $request->ip(),
            ]);
            abort(403, 'Firma inválida');
        }

        $fecha_transaccion = Carbon::parse($request->transaction_date);

        // Fecha límite inclusiva del día (ver Actividad::pagoDentroDeFecha). Antes
        // usaba lessThanOrEqualTo contra fechaLimitePago a las 00:00, con lo que un
        // pago del propio día límite (con hora > 00:00) igual quedaba fuera de plazo.
        if($inscripcion->actividad->pagoDentroDeFecha($fecha_transaccion)) {
            $payment->updateUserStatus();
            Mail::to($inscripcion->persona->mail)->queue(new MailInscripcionConfirmada($inscripcion));
            \Log::info('Confirmación de pago recibida y aplicada', [
                'idInscripcion' => $idInscripcion,
                'reference_sale' => $request->input('reference_sale'),
                'state_pol' => $request->input('state_pol'),
            ]);
        }
        else {
            Mail::to($inscripcion->persona->mail)->queue(new MailInscripcionPagoFueraDeFecha($inscripcion));
            \Log::info('Confirmación de pago recibida fuera de fecha límite', [
                'idInscripcion' => $idInscripcion,
                'reference_sale' => $request->input('reference_sale'),
                'state_pol' => $request->input('state_pol'),
            ]);
        }
    }
}
