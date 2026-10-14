<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Consulta a Stripe si un PaymentIntent fue reembolsado.
 *
 * Usa la API key ya seteada (\Stripe\Stripe::setApiKey) por quien la llama, que
 * es la del país del webhook. Se consulta la lista de cargos del PI en vez de
 * `latest_charge` porque stripe-php no fija versión de API y cada cuenta tiene
 * la suya (las viejas no exponen `latest_charge`).
 *
 * Separado en un servicio para poder reemplazarlo en tests sin llamar a Stripe.
 */
class StripeReembolsos
{
    /**
     * true si el PI no tiene ningún cargo cobrado que siga vigente, es decir,
     * todo lo cobrado fue reembolsado por completo. Ante un error de la API
     * devuelve false (no bloquea el alta del pago): si después llega
     * charge.refunded, ese evento desmarca la inscripción.
     */
    public function piReembolsado(?string $paymentIntentId): bool
    {
        if (!$paymentIntentId) {
            return false;
        }

        try {
            $cargos = \Stripe\Charge::all(['payment_intent' => $paymentIntentId, 'limit' => 10]);
        } catch (\Exception $e) {
            Log::error('StripeReembolsos: no se pudo consultar cargos de ' . $paymentIntentId . ': ' . $e->getMessage());
            return false;
        }

        $cobrados = array_filter($cargos->data, function ($c) {
            return $c->paid && $c->status === 'succeeded';
        });

        if (empty($cobrados)) {
            return false;
        }

        foreach ($cobrados as $cargo) {
            if (empty($cargo->refunded)) {
                return false;
            }
        }

        return true;
    }
}
