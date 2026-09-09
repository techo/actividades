<?php

namespace App\Services;

use App\Donation;
use App\DonationInvoice;
use App\DonationPreset;
use App\DonationSubscription;
use App\Persona;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Arma el payload del dashboard de impacto de un donante (tres tarjetas).
 *
 * Fuente de datos (todo local, sin llamar a Stripe):
 *   - donations              → aportes de única vez (status succeeded)
 *   - donation_invoices      → cobros exitosos de suscripciones (ledger)
 *   - donation_subscriptions → suscripción vigente (monto e interval actuales)
 *
 * Reglas de producto (ver config/donaciones_impacto.php):
 *   - MONEDA LOCAL: todo se expresa en la moneda del donante; no hay FX.
 *     Si aportó en varias monedas, se toma una moneda "primaria" y se suma
 *     sólo esa (documentado en resolverMoneda()).
 *   - Las donaciones ligadas a una inscripción (inscripcion_id != null) NO
 *     cuentan como donación de impacto: son pagos de actividades.
 *   - Costos y promedios de impacto son constantes de config (hoy dummy).
 */
class DonationImpactService
{
    /** @var array Config de constantes de impacto. */
    private $cfg;

    public function __construct()
    {
        $this->cfg = config('donaciones_impacto');
    }

    /**
     * Payload completo para el donante autenticado.
     */
    public function paraDonante(Persona $persona): array
    {
        $personId = $persona->idPersona;

        $moneda      = $this->resolverMoneda($persona, $personId);
        $exponente   = $this->exponente($moneda);
        $totalMenor  = $this->totalAportadoMenor($personId, $moneda);
        $totalMayor  = $totalMenor / (10 ** $exponente);
        $mesesActivos = $this->mesesActivosConsecutivos($personId);

        return [
            'currency'       => $moneda,
            'total_aportado' => [
                'minor'    => $totalMenor,
                'major'    => round($totalMayor, 2),
                'currency' => $moneda,
            ],
            'reloj_impacto' => $this->tarjetaReloj($mesesActivos),
            'impacto_m2'    => $this->tarjetaImpactoM2($totalMayor, $moneda),
            'logistica'     => $this->tarjetaLogistica($personId, $moneda, $exponente),
        ];
    }

    // =========================================================================
    // Tarjeta A — Reloj de impacto global
    // =========================================================================

    private function tarjetaReloj(int $meses): array
    {
        $prom = $this->cfg['promedios_org'];

        $viviendas   = $meses * $prom['viviendas'];
        $voluntarios = $meses * $prom['voluntarios'];
        $mesas       = $meses * $prom['mesas'];

        // Framing colectivo (no "vos lograste"): la red de TECHO logró X en el
        // tiempo que el donante lleva activo.
        if ($meses <= 0) {
            $intro = 'Sumate como socio: cada mes, la red de TECHO transforma '
                . 'territorios en toda Latinoamérica.';
        } else {
            $intro = 'El impacto masivo no se logra solo. En los ' . $meses
                . ' meses que llevás como socio activo, la red de TECHO ha '
                . 'logrado en toda Latinoamérica:';
        }

        return [
            'meses_activos' => $meses,
            'titulo'        => 'Desde que llegaste...',
            'intro'         => $intro,
            'viviendas'     => $viviendas,
            'voluntarios'   => $voluntarios,
            'mesas'         => $mesas,
        ];
    }

    // =========================================================================
    // Tarjeta B — Impacto en metros cuadrados
    // =========================================================================

    private function tarjetaImpactoM2(float $totalMayor, string $moneda): array
    {
        $costos  = $this->costos($moneda);
        $metaM2  = (float) $this->cfg['meta_m2_vivienda'];
        $costoM2 = (float) $costos['m2'];
        $costoViv = (float) $costos['vivienda'];

        $metros = $costoM2 > 0 ? $totalMayor / $costoM2 : 0.0;

        // Cuántas viviendas completas financió (hito) y progreso hacia la próxima.
        $viviendasFinanciadas = $costoViv > 0 ? (int) floor($totalMayor / $costoViv) : 0;

        if ($viviendasFinanciadas >= 1) {
            $aporteRestante = $totalMayor - ($viviendasFinanciadas * $costoViv);
            $metrosRestantes = $costoM2 > 0 ? $aporteRestante / $costoM2 : 0.0;
            $porcentajeBarra = $metaM2 > 0 ? ($metrosRestantes / $metaM2) * 100 : 0.0;
            $mensaje = '¡Felicidades! Financiaste el equivalente a '
                . $viviendasFinanciadas . ' vivienda' . ($viviendasFinanciadas > 1 ? 's' : '')
                . '. ¡Vamos por más!';
        } else {
            $porcentajeBarra = $metaM2 > 0 ? (round($metros, 1) / $metaM2) * 100 : 0.0;
            $mensaje = null;
        }

        return [
            'metros_cuadrados'      => round($metros, 1),
            'meta_m2'               => $metaM2,
            'viviendas_financiadas' => $viviendasFinanciadas,
            'porcentaje_barra'      => round(min(max($porcentajeBarra, 0), 100), 1),
            'costo_m2'              => $costoM2,
            'currency'              => $moneda,
            'mensaje'               => $mensaje,
        ];
    }

    // =========================================================================
    // Tarjeta C — Logística cíclica
    // =========================================================================

    private function tarjetaLogistica(int $personId, string $moneda, int $exponente): array
    {
        $split = $this->cfg['logistica_split'];

        // Monto mensual del donante (suscripción vigente), en unidad mayor.
        $montoMensualMenor = $this->montoMensualMenor($personId, $moneda);
        $montoMensualMayor = $montoMensualMenor !== null
            ? $montoMensualMenor / (10 ** $exponente)
            : null;

        // Rotación por mes calendario para que el contenido no aburra.
        // Igual que el algoritmo cíclico del documento (mes % 3).
        $categorias = [
            0 => [
                'key'    => 'herramientas',
                'icono'  => '🛠️',
                'titulo' => 'Equipás a la comunidad',
                'texto'  => 'Tu aporte de este mes ayuda a financiar kits de trabajo '
                    . '(palas, cascos, guantes, clavos) para la construcción.',
            ],
            1 => [
                'key'    => 'fletes',
                'icono'  => '🚚',
                'titulo' => 'Movés los materiales',
                'texto'  => 'Tu aporte de este mes ayuda a cubrir los fletes que llevan '
                    . 'la madera y los paneles al asentamiento.',
            ],
            2 => [
                'key'    => 'voluntariado',
                'icono'  => '🤝',
                'titulo' => 'Movés a los voluntarios',
                'texto'  => 'Tu aporte de este mes ayuda a cubrir el transporte, la '
                    . 'alimentación y los seguros de los voluntarios en territorio.',
            ],
        ];

        $cat = $categorias[Carbon::now()->month % 3];
        $porcentaje = $split[$cat['key']] ?? 0.0;

        // Parte del aporte mensual asignada a esta categoría (en moneda local).
        $montoCategoriaMayor = $montoMensualMayor !== null
            ? round($montoMensualMayor * $porcentaje, 2)
            : null;

        return [
            'categoria'          => $cat['key'],
            'icono'              => $cat['icono'],
            'titulo'             => $cat['titulo'],
            'texto'              => $cat['texto'],
            'porcentaje'         => (int) round($porcentaje * 100),
            'monto_mensual'      => $montoMensualMayor,   // null si no hay suscripción vigente
            'monto_categoria'    => $montoCategoriaMayor, // parte asignada a la categoría
            'currency'           => $moneda,
        ];
    }

    // =========================================================================
    // Helpers de datos
    // =========================================================================

    /**
     * Moneda "primaria" del donante:
     *   1. la de su suscripción vigente (no terminal), si tiene;
     *   2. si no, la de su donación de única vez más reciente;
     *   3. si no, la del preset de su país;
     *   4. si no, el fallback global.
     * Se suma sólo esa moneda (no se mezclan monedas, no hay FX).
     */
    private function resolverMoneda(Persona $persona, int $personId): string
    {
        $sub = DonationSubscription::where('person_id', $personId)
            ->whereNotIn('status', DonationSubscription::TERMINAL_STATUSES)
            ->orderByDesc('created_at')
            ->first();
        if ($sub && $sub->currency) {
            return strtolower($sub->currency);
        }

        $donacion = Donation::where('person_id', $personId)
            ->where('status', Donation::STATUS_SUCCEEDED)
            ->whereNull('inscripcion_id')
            ->orderByDesc('created_at')
            ->first();
        if ($donacion && $donacion->currency) {
            return strtolower($donacion->currency);
        }

        $preset = $persona->idPais
            ? DonationPreset::where('id_pais', $persona->idPais)->first()
            : null;
        if ($preset && $preset->currency) {
            return strtolower($preset->currency);
        }

        return $this->cfg['moneda_fallback'];
    }

    /**
     * Total aportado (única vez succeeded + cobros recurrentes del ledger) en la
     * moneda dada, en UNIDAD MENOR. Excluye pagos de inscripción.
     */
    private function totalAportadoMenor(int $personId, string $moneda): int
    {
        $unicos = (int) Donation::where('person_id', $personId)
            ->where('status', Donation::STATUS_SUCCEEDED)
            ->whereNull('inscripcion_id')
            ->where('currency', $moneda)
            ->sum('amount');

        $recurrentes = (int) DonationInvoice::where('person_id', $personId)
            ->where('currency', $moneda)
            ->sum('amount_paid');

        return $unicos + $recurrentes;
    }

    /**
     * Monto de la suscripción vigente del donante en la moneda dada (unidad
     * menor), o null si no tiene una suscripción no terminal en esa moneda.
     */
    private function montoMensualMenor(int $personId, string $moneda): ?int
    {
        $sub = DonationSubscription::where('person_id', $personId)
            ->whereNotIn('status', DonationSubscription::TERMINAL_STATUSES)
            ->where('currency', $moneda)
            ->orderByDesc('created_at')
            ->first();

        return $sub ? (int) $sub->amount : null;
    }

    /**
     * Meses consecutivos con al menos un cobro exitoso, contando hacia atrás
     * desde el mes más reciente con cobro. Reconstruido desde el ledger.
     *
     * Si el donante cortó y volvió, la racha refleja sólo el tramo vigente.
     * Un donante sólo de única vez (sin cobros recurrentes) da 0.
     */
    private function mesesActivosConsecutivos(int $personId): int
    {
        // Meses (YYYY-MM) distintos con cobro exitoso, más nuevo primero.
        $meses = DonationInvoice::where('person_id', $personId)
            ->whereNotNull('paid_at')
            ->orderByDesc('paid_at')
            ->pluck('paid_at')
            ->map(function ($fecha) {
                return Carbon::parse($fecha)->format('Y-m');
            })
            ->unique()
            ->values();

        if ($meses->isEmpty()) {
            return 0;
        }

        $racha  = 1;
        $cursor = Carbon::createFromFormat('Y-m', $meses[0])->startOfMonth();

        for ($i = 1; $i < $meses->count(); $i++) {
            $esperado = $cursor->copy()->subMonth()->format('Y-m');
            if ($meses[$i] === $esperado) {
                $racha++;
                $cursor->subMonth();
            } else {
                break; // hueco → se corta la racha vigente
            }
        }

        return $racha;
    }

    // =========================================================================
    // Helpers de config
    // =========================================================================

    private function costos(string $moneda): array
    {
        return $this->cfg['costos'][$moneda]
            ?? $this->cfg['costos'][$this->cfg['moneda_fallback']];
    }

    private function exponente(string $moneda): int
    {
        return in_array($moneda, $this->cfg['monedas_sin_decimales'], true) ? 0 : 2;
    }
}
