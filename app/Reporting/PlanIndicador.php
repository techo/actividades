<?php

namespace App\Reporting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Valor PLANIFICADO (Plan) de un indicador institucional, por país/año/mes.
 *
 * La definición del indicador (qué mide, de qué vista `reporting_*` sale el
 * "Real") sigue viviendo únicamente en MetricRegistry — este modelo solo guarda
 * la meta, referenciándola por su `metric_key`. No se duplica ninguna regla de
 * negocio acá.
 *
 * Nunca se actualiza un valor in place: guardarPlan() cierra la versión vigente
 * anterior (vigente=false) y crea una fila nueva con version+1, para conservar
 * el historial de qué se planificó y cuándo cambió.
 */
class PlanIndicador extends Model
{
    protected $table = 'reporting_plan_indicador';

    protected $fillable = [
        'metric_key', 'idPais', 'idOficina', 'anio', 'mes', 'granularidad', 'periodo',
        'valor_planificado', 'version', 'vigente', 'created_by', 'aprobado_by',
    ];

    protected $casts = [
        'valor_planificado' => 'float',
        'vigente'           => 'boolean',
    ];

    public function creador()
    {
        return $this->belongsTo(\App\Persona::class, 'created_by', 'idPersona');
    }

    /** Query del scope (una fila lógica de plan): indicador + país + año + granularidad + período. */
    private static function scope(string $metricKey, int $idPais, int $anio, string $granularidad, ?int $periodo)
    {
        return static::where('metric_key', $metricKey)
            ->where('idPais', $idPais)
            ->where('anio', $anio)
            ->where('granularidad', $granularidad)
            ->where('periodo', $periodo);
    }

    /** Valor vigente de un indicador para el período dado, o null si nunca se planificó. */
    public static function vigentePara(string $metricKey, int $idPais, int $anio, string $granularidad, ?int $periodo): ?self
    {
        return static::scope($metricKey, $idPais, $anio, $granularidad, $periodo)
            ->where('vigente', true)
            ->orderByDesc('version')
            ->first();
    }

    /**
     * Todos los planes vigentes de un país/año/granularidad, indexados por
     * "metric_key|periodo" (periodo null -> 'A'). Una sola query para armar la
     * matriz completa sin N+1.
     */
    public static function vigentesIndexados(int $idPais, array $anios, string $granularidad): array
    {
        if (empty($anios)) {
            return [];
        }

        $filas = static::where('idPais', $idPais)
            ->whereIn('anio', $anios)
            ->where('granularidad', $granularidad)
            ->where('vigente', true)
            ->orderByDesc('version')
            ->get();

        $index = [];
        foreach ($filas as $fila) {
            $clave = $fila->metric_key . '|' . $fila->anio . '|' . ($fila->periodo ?? 'A');
            if (!isset($index[$clave])) { // la primera es la de mayor version
                $index[$clave] = (float) $fila->valor_planificado;
            }
        }
        return $index;
    }

    /**
     * Guarda un nuevo valor planificado como una versión nueva, sin pisar el
     * anterior.
     *
     * Va dentro de una transacción con lockForUpdate sobre el scope: sin eso, dos
     * admins guardando el mismo metric_key/idPais/anio/mes casi simultáneamente
     * leían el mismo "anterior" y creaban dos filas vigentes en paralelo, rompiendo
     * el invariante "una sola vigente por scope". El lock serializa esos guardados.
     */
    public static function guardarPlan(string $metricKey, int $idPais, int $anio, string $granularidad, ?int $periodo, float $valor, int $creadoPor): self
    {
        // Defensa en profundidad: el metric_key ya se valida en el FormRequest,
        // pero el modelo no debe aceptar una key que no exista en MetricRegistry
        // (ej. si se lo llama desde un seeder/command en el futuro).
        if (!MetricRegistry::existe($metricKey)) {
            throw new \InvalidArgumentException("metric_key desconocido: {$metricKey}");
        }
        if (!GranularidadPlan::periodoValido($granularidad, $periodo)) {
            throw new \InvalidArgumentException("período inválido para granularidad {$granularidad}: " . var_export($periodo, true));
        }

        return DB::transaction(function () use ($metricKey, $idPais, $anio, $granularidad, $periodo, $valor, $creadoPor) {
            $anterior = static::scope($metricKey, $idPais, $anio, $granularidad, $periodo)
                ->where('vigente', true)
                ->orderByDesc('version')
                ->lockForUpdate()
                ->first();

            if ($anterior) {
                $anterior->vigente = false;
                $anterior->save();
            }

            return static::create([
                'metric_key'        => $metricKey,
                'idPais'            => $idPais,
                'anio'              => $anio,
                'granularidad'      => $granularidad,
                'periodo'           => $periodo,
                // Compat: las filas mensuales conservan mes = periodo.
                'mes'               => $granularidad === 'mensual' ? $periodo : null,
                'valor_planificado' => $valor,
                'version'           => $anterior ? $anterior->version + 1 : 1,
                'vigente'           => true,
                'created_by'        => $creadoPor,
            ]);
        });
    }
}
