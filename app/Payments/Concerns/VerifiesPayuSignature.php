<?php

namespace App\Payments\Concerns;

use Illuminate\Http\Request;

/**
 * Verificación de firma para las notificaciones server-to-server de PayU Latam
 * ("Confirmation URL"). Documentación oficial:
 * https://developers.payulatam.com/latam/en/docs/integrations/confirmation-url.html
 *
 * Antes de este trait, PagosController::confirmation() marcaba una inscripción
 * como pagada mirando únicamente `state_pol`/`polTransactionState` en el POST
 * entrante, sin verificar que la notificación viniera realmente de PayU. La ruta
 * no tiene auth ni CSRF (es un webhook público), así que cualquiera podía forjar
 * el POST y marcar cualquier inscripción como pagada sin pagar. Ver
 * docs/master-plan-estabilizacion.md, hallazgo A.
 *
 * Reglas de PayU (tal cual la documentación oficial):
 * - La firma se calcula con los valores QUE VIENEN EN EL POST de confirmación
 *   (merchant_id, reference_sale, value, currency, state_pol) — nunca con los
 *   valores de nuestra base de datos. El único dato que sale de nuestra config
 *   es el apiKey (el secreto), que nunca debe venir del request.
 * - El campo `value` se formatea de una manera particular antes de firmarlo:
 *   si el segundo decimal es 0, se usa un solo decimal (150.00 -> 150.0);
 *   si no, se mantienen los dos decimales (150.25 -> 150.25).
 *
 * Se usa como trait (no como clase base) porque PayU.php y DefaultPago.php son
 * dos implementaciones casi idénticas de PaymentGateway sin una jerarquía común
 * (deuda ya señalada en el Master Plan, sección 2.4, pendiente de consolidación
 * en la Etapa 2). Hasta que se unifiquen, esta pieza de seguridad crítica no
 * debe duplicarse a mano en cada una — vive acá una sola vez y ambas la reusan.
 */
trait VerifiesPayuSignature
{
    /**
     * Verifica que la notificación de confirmación:
     * 1) tenga una firma (`sign`) válida, calculada con el apiKey de este país, y
     * 2) corresponda a ESTA inscripción puntual (reference_sale coincide con el
     *    código generado para $this->inscripcion) — esto evita que una notificación
     *    válida de OTRA transacción (por ejemplo, una compra real de $1 del propio
     *    atacante) se reenvíe contra un idInscripcion distinto en la URL.
     *
     * @return bool
     */
    public function verifyConfirmationSignature()
    {
        if (!($this->request instanceof Request)) {
            return false;
        }

        $receivedSign = (string) $this->request->input('sign', '');
        if ($receivedSign === '') {
            return false;
        }

        $config = $this->getConfig();
        if (empty($config->api_key)) {
            // Sin api_key configurada no hay forma de verificar nada: no se puede
            // confiar en la notificación, se rechaza (fail closed, no fail open).
            return false;
        }

        $referenceSale = (string) $this->request->input('reference_sale', '');
        if ($referenceSale === '' || $referenceSale !== $this->referenceCode()) {
            return false;
        }

        $merchantId = (string) $this->request->input('merchant_id', '');
        $value      = (string) $this->request->input('value', '');
        $currency   = (string) $this->request->input('currency', '');
        $statePol   = (string) $this->request->input('state_pol', '');

        $expectedSign = md5(implode('~', [
            $config->api_key,
            $merchantId,
            $referenceSale,
            $this->formatPayuValue($value),
            $currency,
            $statePol,
        ]));

        return hash_equals(strtolower($expectedSign), strtolower($receivedSign));
    }

    /**
     * Puerto directo de la regla de formato de `value` que usa PayU para la firma
     * (ver ejemplo de implementación oficial en la documentación citada arriba).
     *
     * @param string $value
     * @return string
     */
    private function formatPayuValue($value)
    {
        $parts = explode('.', $value);

        if (!isset($parts[1]) || $parts[1] === '') {
            return $parts[0] . '.0';
        }

        if (strlen($parts[1]) > 1 && $parts[1][1] !== '0') {
            return $parts[0] . '.' . substr($parts[1], 0, 2);
        }

        return $parts[0] . '.' . $parts[1][0];
    }
}
