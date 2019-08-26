<?php
namespace App\Payments;


use App\Inscripcion;
use App\Mail\MailInscripcionConfirmada;
use App\Mail\MailInscripcionPagoFueraDeFecha;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PayU implements PaymentGateway
{
    public $request;
    public $actividad;
    public $persona;
    public $inscripcion;
    public $monto;

    /* Transacción de confirmación de ejemplo
    response_code_pol=1&phone=1155233214&additional_value=0.00&test=0&transaction_date=2019-08-02+15%3A12%3A04&cc_number=************6661&cc_holder=APPROVED&error_code_bank=0&billing_country=AR&bank_referenced_name=&description=Encuestamientos%2C+18311%2C+281%2C+Buenos+Aires-GBA%2C+08%2F08%2F2019&administrative_fee_tax=0.00&value=200.00&administrative_fee=0.00&payment_method_type=2&office_phone=&account_id=512322&email_buyer=marcos.wolff%40techo.org&response_message_pol=APPROVED&error_message_bank=&shipping_city=&transaction_id=52f5e22d-5f2f-4547-ab26-c566ccbb6763&sign=198653c1d714c7f27b64ea01d81008fa&operation_date=2019-08-02+15%3A12%3A04&tax=0.00&transaction_bank_id=NPS-011111&payment_method=257&billing_address=&payment_method_name=VISA&pseCycle=null&pse_bank=&state_pol=4&date=2019.08.02+03%3A12%3A04&nickname_buyer=&reference_pol=846116081&currency=ARS&risk=0.0&shipping_address=&bank_id=257&payment_request_state=A&customer_number=&administrative_fee_base=0.00&attempts=1&merchant_id=508029&exchange_rate=1.00&shipping_country=AR&installments_number=1&franchise=VISA&payment_method_id=2&extra1=&extra2=&antifraudMerchantId=&extra3=&commision_pol_currency=&nickname_seller=&ip=172.18.49.47&commision_pol=0.00&airline_code=&billing_city=&pse_reference1=&cus=52f5e22d-5f2f-4547-ab26-c566ccbb6763&reference_sale=Encuestamientos-Voluntario-31925539-18311-1269367&authorization_code=NPS-011111&pse_reference3=&pse_reference2=
    */

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
            $this->inscripcion->pago = 1;
            $this->inscripcion->montoPago = (float)$this->request->value;
            $this->inscripcion->moneda = $this->request->currency;
            $this->inscripcion->fechaPago = Carbon::now();
            return $this->inscripcion->save();
        }
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