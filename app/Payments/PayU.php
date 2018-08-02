<?php
namespace App\Payments;


use App\Inscripcion;
use Illuminate\Http\Request;

class PayU implements PaymentGateway
{
    public $request;
    public $actividad;
    public $persona;
    public $inscripcion;
    private $random; // testing

    public function __construct(Inscripcion $inscripcion)
    {
//        $this->request = null;
        $this->inscripcion = $inscripcion;
        $this->actividad = $this->inscripcion->actividad;
        $this->persona = $this->inscripcion->persona;
        $this->random = rand(1,100000);
    }

    public function success()
    {
        if ($this->request instanceof Request) {
            return $this->request->lapResponseCode === 'APPROVED';
        }

        return response('Se debe llamar a setRequest con una instancia de Illuminate\Http\Request', 428);
    }

    public function error()
    {
        if ($this->request instanceof Request) {
            return $this->request->lapResponseCode !== 'APPROVED';
        }
        return response('Se debe llamar a setRequest con una instancia de Illuminate\Http\Request', 428);
    }

    public function message()
    {
        if ($this->request instanceof Request) {
            if (array_key_exists($this->request->lapResponseCode, config('payments.payu.messages'))) {
                return config('payments.payu.messages.' . $this->request->lapResponseCode);
            }
            return '';
        }
        return response('Se debe llamar a setRequest con una instancia de Illuminate\Http\Request', 428);
    }

    public function signature()
    {
        return md5(env('PAYU_APIKEY') . '~' .
            env('PAYU_MERCHANT_ID') . '~' .
            $this->referenceCode() . '~' .
            $this->actividad->costo . '~' .
            $this->actividad->moneda);
    }

    public function referenceCode()
    {
        return $this->actividad->idActividad .
            '|' .
            auth()->user()->idPersona .
            '|' .
            $this->inscripcion->idInscripcion .  '|' .
            $this->random;

    }

    public function url()
    {
        return config('payments.payu.url');
    }

    public function method()
    {
        return config('payments.payu.method');
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }
}