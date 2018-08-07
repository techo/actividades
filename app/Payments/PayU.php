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

    /**
     * PayU constructor.
     * @param Inscripcion $inscripcion
     */
    public function __construct(Inscripcion $inscripcion)
    {
        $this->inscripcion = $inscripcion;
        $this->actividad = $this->inscripcion->actividad;
        $this->persona = $this->inscripcion->persona;
        $this->random = rand(1,100000);
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
        $config = $this->config();

        return md5($config->api_key . '~' .
            $config->merchant_id . '~' .
            $this->referenceCode() . '~' .
            $this->actividad->costo . '~' .
            $this->actividad->moneda);
    }

    /**
     * Retorna el reference code requerido por PayU. Puede construirse de cualquier manera,
     * siempre y cuando sea único por transacción
     * @return string
     */
    public function referenceCode()
    {

        return $this->actividad->tipo->nombre . '-Voluntario-'
            . $this->persona->dni . '-'
            . $this->actividad->nombreActividad . '-'
            . $this->actividad->idActividad . '~|'
            . $this->inscripcion->idInscripcion .  '-'
            . $this->random;

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
        $mySignature = $this->signature();
        if ($this->request->polTransactionState === '4' && $mySignature === $this->request->signature) {
            $this->inscripcion->pago = 1;
            $this->montoPago = $this->request->TX_VALUE;
            $this->inscripcion->estado = "Confirmado";
            $this->moneda = $this->request->currency;
            $this->fechaPago = Carbon::now();
            return $this->inscripcion->save();
        }
        return false;
    }

    public function config()
    {
        return json_decode($this->actividad->pais->config_pago);
    }
}