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

        if($request->filled('processingDate'))
            $fecha_transaccion = Carbon::parse($request->processingDate);

        if ($payment->success() && !$inscripcion->actividad->fechaLimitePago ||
            $payment->success() && $fecha_transaccion->lessThanOrEqualTo($inscripcion->actividad->fechaLimitePago)) {
            return view('inscripciones.pagada', ['inscripcion' => $inscripcion, 'actividad' => $payment->actividad]);
        }
        elseif ($payment->success() && $fecha_transaccion->greaterThan($inscripcion->actividad->fechaLimitePago)) {
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

        $fecha_transaccion = Carbon::parse($request->transaction_date);

        if(!$inscripcion->actividad->fechaLimitePago ||
            $inscripcion->actividad->fechaLimitePago && $fecha_transaccion->lessThanOrEqualTo($inscripcion->actividad->fechaLimitePago)) {
            $payment->updateUserStatus();
            Mail::to($inscripcion->persona->mail)->queue(new MailInscripcionConfirmada($inscripcion));
            \Log::info('Confirmación recibida exitosamente desde PAYU: ' . $request);
        }
        else {
            Mail::to($inscripcion->persona->mail)->queue(new MailInscripcionPagoFueraDeFecha($inscripcion));
            \Log::info('Confirmación recibida fuera de fecha desde PAYU: ' . $request);
        }
    }
}
