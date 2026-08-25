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

        if (!$this->socioService->esSocio($persona)) {
            return false;
        }

        $inscripcion->exento_pago   = true;
        $inscripcion->exento_motivo = self::MOTIVO;
        $inscripcion->exento_at     = Carbon::now();

        return true;
    }
}
