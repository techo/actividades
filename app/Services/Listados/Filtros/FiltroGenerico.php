<?php

namespace App\Services\Listados\Filtros;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Aplica una condición campo·operador·valor sobre el query de un listado, sin
 * una clase por campo. El descriptor del campo (type + sql) viene de
 * CatalogoListado::filterableFields(); el `sql` puede ser una columna real o un
 * marcador que este dispatcher sabe resolver:
 *
 *   Persona.dni            columna/expresión directa del SELECT/joins
 *   __edad                 TIMESTAMPDIFF sobre Persona.fechaNacimiento
 *   __subquery:{name}      whereExists sobre una relación (jornadas / nombreGrupo)
 *   __custom:{id}          whereExists sobre listado_columna_valores
 *   __pregunta:{id}        whereExists sobre la tabla de respuestas del listado
 *
 * Se usa whereExists (no join) para custom/pregunta: así una fila con N
 * etiquetas/respuestas no se duplica y paginate()/count() cuentan bien.
 */
class FiltroGenerico
{
    /**
     * ¿El campo está registrado como filtrable? (meta = filterableFields)
     */
    public static function soporta($key, array $meta): bool
    {
        return isset($meta[$key]) && is_array($meta[$key]) && isset($meta[$key]['sql']);
    }

    /**
     * ¿El valor tiene forma de condición avanzada {condicion, valor}?
     */
    public static function esCondicion($value): bool
    {
        return is_array($value) && array_key_exists('condicion', $value) && array_key_exists('valor', $value);
    }

    /**
     * @param array $meta  filterableFields + la clave reservada '__ctx' con
     *                     ['record_sql' => 'Inscripcion.idInscripcion',
     *                      'pregunta' => ['table'=>..., 'fk'=>..., 'col'=>...]|null]
     */
    public static function apply(Builder $query, $key, $value, array $meta): Builder
    {
        $descriptor = $meta[$key];
        $type = $descriptor['type'] ?? 'text';
        $sql = $descriptor['sql'];
        $op = $value['condicion'];
        $valor = $value['valor'];

        if (!Operadores::permitido($type, $op)) {
            return $query; // operador no válido para el tipo: se ignora
        }

        $ctx = $meta['__ctx'] ?? [];

        if (strpos($sql, '__custom:') === 0) {
            return static::aplicarCustom($query, (int) substr($sql, strlen('__custom:')), $type, $op, $valor, $ctx);
        }
        if (strpos($sql, '__pregunta:') === 0) {
            return static::aplicarPregunta($query, (int) substr($sql, strlen('__pregunta:')), $op, $valor, $ctx);
        }
        if (strpos($sql, '__subquery:') === 0) {
            return static::aplicarSubquery($query, substr($sql, strlen('__subquery:')), $op, $valor, $ctx);
        }
        if ($sql === '__edad') {
            return static::comparar($query, DB::raw('TIMESTAMPDIFF(YEAR, Persona.fechaNacimiento, CURDATE())'), $op, $valor);
        }

        // Columna/expresión directa.
        return static::comparar($query, $sql, $op, $valor);
    }

    /**
     * Comparación genérica sobre una columna/expresión. Acepta tanto el
     * Eloquent\Builder principal como el Query\Builder de una subconsulta
     * (whereExists), por eso no se tipa el parámetro.
     */
    private static function comparar($query, $columna, $op, $valor)
    {
        switch ($op) {
            case 'in':
                return $query->whereIn($columna, static::listaValores($valor));
            case 'like':
                return $query->where($columna, 'like', '%' . $valor . '%');
            case 'between':
                $rango = static::listaValores($valor);
                if (count($rango) === 2) {
                    $query->whereBetween($columna, [$rango[0], $rango[1]]);
                }
                return $query;
            default: // = <> < <= > >=
                return $query->where($columna, $op, $valor);
        }
    }

    private static function aplicarCustom(Builder $query, int $columnaId, string $type, $op, $valor, array $ctx): Builder
    {
        $recordSql = $ctx['record_sql'] ?? null;
        if (!$recordSql) {
            return $query;
        }

        // Casilla: marcada = existe fila (valor '1'); desmarcada = sin fila.
        if ($type === 'bool') {
            $marcada = $valor && $valor !== '0' && $valor !== 0 && $valor !== false;
            $metodo = $marcada ? 'whereExists' : 'whereNotExists';
            return $query->{$metodo}(function ($sub) use ($columnaId, $recordSql) {
                $sub->from('listado_columna_valores as lcv')
                    ->whereColumn('lcv.record_id', $recordSql)
                    ->where('lcv.columna_id', $columnaId);
            });
        }

        // Etiquetas (JSON array de strings): contains / not_contains.
        if ($type === 'multi') {
            $metodo = $op === 'not_contains' ? 'whereNotExists' : 'whereExists';
            return $query->{$metodo}(function ($sub) use ($columnaId, $recordSql, $valor) {
                $sub->from('listado_columna_valores as lcv')
                    ->whereColumn('lcv.record_id', $recordSql)
                    ->where('lcv.columna_id', $columnaId)
                    ->where('lcv.valor', 'like', '%"' . $valor . '"%');
            });
        }

        // Resto (estado, texto, fecha, persona, número): whereExists comparando lcv.valor.
        $columnaValor = static::valorCasteado('lcv.valor', $type);

        return $query->whereExists(function ($sub) use ($columnaId, $recordSql, $columnaValor, $op, $valor) {
            $sub->from('listado_columna_valores as lcv')
                ->whereColumn('lcv.record_id', $recordSql)
                ->where('lcv.columna_id', $columnaId);
            static::comparar($sub, $columnaValor, $op, $valor);
        });
    }

    private static function aplicarPregunta(Builder $query, int $preguntaId, $op, $valor, array $ctx): Builder
    {
        $recordSql = $ctx['record_sql'] ?? null;
        $fuente = $ctx['pregunta'] ?? null;
        if (!$recordSql || !$fuente) {
            return $query;
        }

        return $query->whereExists(function ($sub) use ($preguntaId, $recordSql, $fuente, $op, $valor) {
            $sub->from($fuente['table'] . ' as resp')
                ->whereColumn('resp.' . $fuente['fk'], $recordSql)
                ->where('resp.pregunta_id', $preguntaId);
            static::comparar($sub, 'resp.' . $fuente['col'], $op, $valor);
        });
    }

    /**
     * Campos que en el SELECT son subqueries con alias (no filtrables por alias
     * en WHERE): se resuelven con whereExists sobre su relación real.
     */
    private static function aplicarSubquery(Builder $query, string $name, $op, $valor, array $ctx): Builder
    {
        $recordSql = $ctx['record_sql'] ?? 'Inscripcion.idInscripcion';

        if ($name === 'nombreGrupo') {
            return $query->whereExists(function ($sub) use ($recordSql, $op, $valor) {
                $sub->from('Grupo_Persona as gp')
                    ->join('Grupo as g', 'g.idGrupo', '=', 'gp.idGrupo')
                    ->whereColumn('gp.idPersona', 'Persona.idPersona')
                    ->whereColumn('gp.idActividad', 'Inscripcion.idActividad');
                static::comparar($sub, 'g.nombre', $op, $valor);
            });
        }

        if ($name === 'jornadas') {
            return $query->whereExists(function ($sub) use ($op, $valor) {
                $sub->from('InscripcionJornada as ij')
                    ->join('Jornada as j', 'j.idJornada', '=', 'ij.idJornada')
                    ->whereColumn('ij.idInscripcion', 'Inscripcion.idInscripcion');
                static::comparar($sub, 'j.nombre', $op, $valor);
            });
        }

        return $query;
    }

    /**
     * Envuelve una columna TEXT en un CAST cuando el tipo requiere comparación
     * numérica o de fecha (los valores custom se guardan como TEXT sin tipar).
     */
    private static function valorCasteado(string $columna, string $type)
    {
        if ($type === 'number') {
            return DB::raw('CAST(' . $columna . ' AS SIGNED)');
        }
        if ($type === 'date') {
            return DB::raw('CAST(' . $columna . ' AS DATE)');
        }
        return $columna;
    }

    private static function listaValores($valor): array
    {
        if (is_array($valor)) {
            return array_values($valor);
        }
        return array_map('trim', explode(',', (string) $valor));
    }
}
