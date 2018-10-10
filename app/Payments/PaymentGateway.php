<?php
namespace App\Payments;


use Illuminate\Http\Request;

interface PaymentGateway
{
    /**
     * Debe retornar true en caso de que el pago se realice exitosamente
     * @return bool|\Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function success();

    /**
     * Debe retornar true en caso de que el pago no se haya concretado
     * @return bool|\Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function error();

    /**
     * Retorna la traducción del resultado de la operación según está definido en /config/payments.php
     * @return string|\Symfony\Component\HttpFoundation\Response
     */
    public function message();

    /**
     * Retorna la URL de pagos definida por PayU y configurada en /config/payments.php
     * @return string
     */
    public function url();

    /**
     * Retorna el método que usa el formulario que se envía a PayU, se define en /config/payments.php
     * @return String
     */
    public function method();

    /**
     * @param Request $request
     * @return mixed
     */
    public function setRequest(Request $request);

    /**
     * Configura el monto a pagar en una transacción específica
     * @param $monto
     * @return
     */
    public function setMonto($monto);

    /**
     * Actualiza el estatus de la inscripción en la DB
     * @return bool
     */
    public function updateUserStatus();

    /**
     * Retorna la configuración establecida en la base de datos
     * @return mixed
     */
    public function getConfig();
}