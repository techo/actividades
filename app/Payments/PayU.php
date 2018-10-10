<?php
namespace App\Payments;


use App\Inscripcion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayU implements PaymentGateway
{
    public $request;
    public $actividad;
    public $persona;
    public $inscripcion;
    public $monto;

    /**
     * PayU constructor.
     * @param Inscripcion $inscripcion
     */
    public function __construct(Inscripcion $inscripcion)
    {
        $this->inscripcion = $inscripcion;
        $this->actividad = $this->inscripcion->actividad;
        $this->persona = $this->inscripcion->persona;
    }

    /**
     * Retorna true si el pago se finalizó con éxito
     * @return bool|\Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function success()
    {
        if ($this->request instanceof Request) {
            return $this->request->lapResponseCode === 'APPROVED';
        }

        return response('Se debe llamar a setRequest con una instancia de Illuminate\Http\Request', 428);
    }

    /**
     * Retorna true si hubo algún error en el pago
     * @return bool|\Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function error()
    {
        if ($this->request instanceof Request) {
            return $this->request->lapResponseCode !== 'APPROVED';
        }
        return response('Se debe llamar a setRequest con una instancia de Illuminate\Http\Request', 428);
    }

    /**
     * Retorna la traducción del resultado de la operación según está definido en /config/payments.php
     * @return string|\Symfony\Component\HttpFoundation\Response
     */
    public function message()
    {
        if ($this->request instanceof Request) {
            if (array_key_exists($this->request->lapResponseCode, config('payments.payu.messages'))) {
                return config('payments.payu.messages.' . $this->request->lapResponseCode);
            }
            return 'Estatus desconocido';
        }
        return response('Se debe llamar a setRequest con una instancia de Illuminate\Http\Request', 428);
    }

    /**
     * Retorna el signature requerido por PayU
     * @return string
     */
    public function signature()
    {
        $config = $this->getConfig();

        return md5($config->api_key . '~' .
            $config->merchant_id . '~' .
            $this->referenceCode() . '~' .
            $this->monto . '~' .
            $this->actividad->moneda);
    }

    /**
     * Retorna el reference code requerido por PayU. Puede construirse de cualquier manera,
     * siempre y cuando sea único por transacción
     * @return string
     */
    public function referenceCode()
    {

        return $this->actividad->tipo->nombre . '-'
            . 'Voluntario-'
            . $this->persona->dni . '-'
            . $this->actividad->idActividad . '-'
            . $this->inscripcion->idInscripcion;

    }

    /**
     * Retorna la URL de pagos definida por PayU y configurada en /config/payments.php
     * @return string
     */
    public function url()
    {
        return config('payments.payu.url');
    }

    /**
     * Retorna el método que usa el formulario que se envía a PayU, se define en /config/payments.php
     * @return String
     */
    public function method()
    {
        return config('payments.payu.method');
    }

    /**
     *
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Actualiza el estatus de la inscripción en la DB
     * @return bool
     */
    public function updateUserStatus()
    {
        if ($this->request->polTransactionState === '4' || $this->request->state_pol === '4') {
            // Log::info('Confirmación: \n' . json_encode($this->request->all()));
            $this->inscripcion->pago = 1;
            $this->inscripcion->montoPago = (float)$this->request->value;
            $this->inscripcion->estado = "Confirmado";
            $this->inscripcion->moneda = $this->request->currency;
            $this->inscripcion->fechaPago = Carbon::now();
            return $this->inscripcion->save();
        }
        return false;
    }

    public function getConfig()
    {
        return json_decode($this->actividad->pais->config_pago);
    }

    public function setMonto($monto)
    {
        $this->monto = $monto;
    }

    public function getMonto()
    {
        return $this->monto;
    }
}