<?php

namespace App\Services\Salesforce;

use App\Actividad;
use App\Inscripcion;
use App\Persona;
use Carbon\Carbon;

/**
 * Aplica la exención de pago por socio sobre una inscripción.
 *
 * Regla: si la actividad es paga (pago == 1) y la persona es socio/donante de
 * TECHO (SocioService, que ya gatea país + feature flag + DNI), la inscripción
 * queda exenta de pago. El cambio se hace EN MEMORIA sobre el modelo; el caller
 * es responsable de persistir con save().
 *
 * Es el único lugar que decide "esta inscripción no paga por socio". Se invoca
 * al crear la inscripción (web + móvil, InscripcionesController::create) y como
 * defensa server-side antes de cobrar (InscripcionStripeController).
 */
class SocioExencionService
{
    const MOTIVO = 'socio_salesforce';

    /** @var SocioService */
    protected $socioService;

    public function __construct(SocioService $socioService)
    {
        $this->socioService = $socioService;
    }

    /**
     * Marca la inscripción como exenta si corresponde.
     *
     * @return bool true si la inscripción quedó (o ya estaba) exenta.
     */
    public function aplicarSiCorresponde(Inscripcion $inscripcion, Actividad $actividad, Persona $persona): bool
    {
        if ($inscripcion->exento_pago) {
            return true; // Ya exenta: idempotente, no re-consulta Salesforce.
        }

        if ((int) $actividad->pago !== 1) {
            return false; // Actividad no paga: no hay nada que eximir.
        }

        // La exención solo aplica a actividades del país configurado (Argentina).
        // Los socios de Salesforce son de Argentina; un socio argentino que se
        // anota a una actividad de otro país (ej. Brasil) SÍ debe pagar.
        $paisSocio = (int) config('services.salesforce.socio_pais_id', 13);
        if ((int) $actividad->idPais !== $paisSocio) {
            return false;
        }

        if (!$this->socioService->esSocio($persona)) {
            return false;
        }

        $inscripcion->exento_pago   = true;
        $inscripcion->exento_motivo = self::MOTIVO;
        $inscripcion->exento_at     = Carbon::now();

        return true;
    }

    /**
     * Re-evalúa la exención de una inscripción YA EXISTENTE y persiste si cambia.
     *
     * Por qué: la exención se materializa al crear la inscripción, pero quien se
     * hace socio después, o se inscribió mientras Salesforce fallaba (fail-closed),
     * quedaba con el pago pendiente para siempre. Se llama en cada punto donde le
     * pediríamos pagar (pantalla de pago, checkout, confirmación del coordinador,
     * recordatorio) y desde el comando socios:reevaluar-exenciones.
     *
     * No toca inscripciones ya pagadas (pago == 1): eso es una devolución, no una
     * exención, y se decide caso a caso.
     *
     * @return bool true si la inscripción quedó (o ya estaba) exenta.
     */
    public function reevaluar(Inscripcion $inscripcion): bool
    {
        if ($inscripcion->exento_pago) {
            return true;
        }

        if ((int) $inscripcion->pago === 1 || $inscripcion->trashed()) {
            return false;
        }

        $actividad = $inscripcion->actividad;
        $persona   = $inscripcion->persona;
        if (!$actividad || !$persona) {
            return false;
        }

        if (!$this->aplicarSiCorresponde($inscripcion, $actividad, $persona)) {
            return false;
        }

        $inscripcion->save();

        return true;
    }
}
