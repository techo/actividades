<?php

namespace App\Http\Controllers;

use App\Inscripcion;
use Carbon\Carbon;
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

        if($request->filled('processingDate'))
            $fecha_transaccion = Carbon::parse($request->processingDate);

        if ($payment->success() && $fecha_transaccion->lessThanOrEqualTo($inscripcion->actividad->fechaLimitePago)) {
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
        $payment->updateUserStatus();
    }
}
