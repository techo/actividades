<?php

namespace App\Http\Controllers;

use App\Actividad;
use App\Inscripcion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PagosController extends Controller
{
    public function response(Request $request, $idInscripcion)
    {
        Log::info('Response: \n' . json_encode($request->all()));
        $inscripcion = Inscripcion::findOrFail($idInscripcion)
            ->with(['pais', 'actividad']);

        $paymentClass = 'App\\Payments\\' . $inscripcion->pais->medio_pago;
        $payment = new $paymentClass($request);
        if ($payment->success) {
            return view('inscripciones.gracias');
        }
        return view('pagos.response')->with('payment', $payment);

    }

    public function confirmation(Request $request)
    {
        Log::info('Confirmación: \n' . json_encode($request->all()));
    }
}
