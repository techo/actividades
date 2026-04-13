<?php

namespace App\Payments;

use App\Inscripcion;
use Illuminate\Http\Request;

/**
 * Stub que implementa PaymentGateway para que confirmarDonacion pueda
 * instanciar la clase cuando el país usa Stripe.
 *
 * El flujo real de Stripe vive en StripeController — estos métodos
 * no se llaman durante ese flujo.
 */
class Stripe implements PaymentGateway
{
    public $inscripcion;
    public $actividad;
    public $persona;

    public function __construct(Inscripcion $inscripcion)
    {
        $this->inscripcion = $inscripcion;
        $this->actividad   = $inscripcion->actividad;
        $this->persona     = $inscripcion->persona;
    }

    public function success()   { return false; }
    public function error()     { return false; }
    public function message()   { return ''; }
    public function url()       { return ''; }
    public function method()    { return 'POST'; }
    public function setMonto($monto) { }
    public function getMonto()  { return $this->actividad->montoMin ?? 0; }

    public function setRequest(Request $request) { }

    public function updateUserStatus() { return false; }

    public function getConfig()
    {
        return json_decode($this->actividad->pais->config_pago);
    }
}
