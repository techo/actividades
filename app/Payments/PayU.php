<?php
namespace App\Payments;


use App\Inscripcion;
use Carbon\Carbon;
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
        $pais = $this->actividad->pais;
        $config = json_decode($pais->config_pago);

        return md5($config->api_key . '~' .
            $config->merchant_id . '~' .
            $this->referenceCode() . '~' .
            $this->actividad->costo . '~' .
            $this->actividad->moneda);
    }

    public function referenceCode()
    {
        return $this->actividad->tipo->nombre . '-Voluntario-'
            . auth()->user()->dni . '-'
            . $this->actividad->nombreActividad . '-'
            . $this->actividad->idActividad . '~|'
            . $this->inscripcion->idInscripcion .  '-'
            . $this->random;

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

    public function updateUserStatus()
    {
        $mySignature = $this->signature();
        if ($this->request->polTransactionState === '4' && $mySignature === $this->request->signature) {
            $this->inscripcion->pago = 1;
            $this->montoPago = $this->request->TX_VALUE;
            $this->moneda = $this->request->currency;
            $this->fechaPago = Carbon::now();
            $this->inscripcion->save();
        }
        return false;
    }
}