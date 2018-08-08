<?php

namespace App\Http\Controllers;

use App\Inscripcion;
use Illuminate\Http\Request;

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

        if ($payment->success()) {
            return view('inscripciones.pagada', ['inscripcion' => $inscripcion, 'actividad' => $payment->actividad]);
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
        $payment->updateUserStatus();
    }
}
