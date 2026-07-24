<?php

namespace App\Services\Listados;

use Illuminate\Support\Facades\DB;

/**
 * Recuento por grupo (facets) de un listado configurable: dado un campo de
 * agrupación y los filtros activos, devuelve cuántos registros hay por cada
 * valor. Alimenta la fila de chips "RECUENTO POR X" del prototipo.
 *
 * Reutiliza el query ya filtrado (misma clase *Search) y le reemplaza el SELECT
 * por una agregación GROUP BY. Los campos custom/pregunta se agregan con un
 * LEFT JOIN a una subconsulta acotada por columna/pregunta, para que las filas
 * sin valor caigan en el bucket `sin_valor` en vez de perderse.
 */
class Facetador
{
    /**
     * @return array{field:string, buckets:array, sin_valor:int}
     */
    public function facetar(string $listKey, array $filtros, string $groupByKey): array
    {
        $meta = $filtros['__filterable'] ?? [];
        $descriptor = $meta[$groupByKey] ?? null;

        $vacio = ['field' => $groupByKey, 'buckets' => [], 'sin_valor' => 0];
        if (!$descriptor) {
            return $vacio;
        }

        $ctx = $meta['__ctx'] ?? [];
        $query = (new ListadoQuery())->builder($listKey, $filtros)->getQuery();

        // El SELECT fijo y el orden del listado no aplican a un conteo agrupado.
        $query->columns = null;
        $query->orders = null;

        $expr = $this->expresionAgrupacion($query, $descriptor['sql'], $ctx);
        if ($expr === null) {
            return $vacio;
        }

        $filas = $query
            ->selectRaw($expr . ' as valor')
            ->selectRaw('COUNT(*) as total')
            ->groupBy(DB::raw($expr))
            ->get();

        $buckets = [];
        $sinValor = 0;
        foreach ($filas as $fila) {
            if ($fila->valor === null || $fila->valor === '') {
                $sinValor += (int) $fila->total;
                continue;
            }
            $buckets[] = [
                'valor' => $fila->valor,
                'label' => (string) $fila->valor,
                'total' => (int) $fila->total,
            ];
        }

        // Mayor a menor, para que los grupos más grandes queden primero.
        usort($buckets, function ($a, $b) {
            return $b['total'] - $a['total'];
        });

        return ['field' => $groupByKey, 'buckets' => $buckets, 'sin_valor' => $sinValor];
    }

    /**
     * Devuelve la expresión SQL por la que agrupar (y aplica los joins que haga
     * falta para los campos custom/pregunta). null si el campo no es agrupable.
     */
    private function expresionAgrupacion($query, string $sql, array $ctx)
    {
        if (strpos($sql, '__custom:') === 0) {
            $id = (int) substr($sql, strlen('__custom:'));
            $recordSql = $ctx['record_sql'] ?? null;
            if (!$recordSql) {
                return null;
            }
            $query->leftJoin(
                DB::raw('(SELECT record_id, valor FROM listado_columna_valores WHERE columna_id = ' . $id . ') as lcvfacet'),
                'lcvfacet.record_id', '=', DB::raw($recordSql)
            );
            return 'lcvfacet.valor';
        }

        if (strpos($sql, '__pregunta:') === 0) {
            $id = (int) substr($sql, strlen('__pregunta:'));
            $recordSql = $ctx['record_sql'] ?? null;
            $fuente = $ctx['pregunta'] ?? null;
            if (!$recordSql || !$fuente) {
                return null;
            }
            $query->leftJoin(
                DB::raw('(SELECT ' . $fuente['fk'] . ' as fk, ' . $fuente['col'] . ' as valor FROM '
                    . $fuente['table'] . ' WHERE pregunta_id = ' . $id . ') as respfacet'),
                'respfacet.fk', '=', DB::raw($recordSql)
            );
            return 'respfacet.valor';
        }

        if ($sql === '__subquery:nombreGrupo') {
            return '(SELECT G.nombre FROM Grupo_Persona GP JOIN Grupo G ON G.idGrupo = GP.idGrupo'
                . ' WHERE GP.idPersona = Persona.idPersona AND GP.idActividad = Inscripcion.idActividad LIMIT 1)';
        }

        if (strpos($sql, '__') === 0) {
            // Otros marcadores (jornadas multivaluado, edad, estado) no se agrupan.
            return null;
        }

        // Columna base directa.
        return $sql;
    }
}
