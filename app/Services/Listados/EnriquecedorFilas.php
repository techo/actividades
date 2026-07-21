<?php

namespace App\Services\Listados;

use App\ActividadPregunta;
use App\InscripcionRespuesta;
use App\ListadoColumna;
use App\ListadoColumnaValor;
use App\Persona;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Inyecta en las filas ya paginadas de un listado los datos de columnas
 * dinámicas: respuestas a preguntas de inscripción (pregunta_{id}) y valores
 * de columnas de seguimiento (custom_{id}).
 *
 * Trabaja sobre la página (10-25 filas): 2-4 queries con whereIn, sin tocar
 * la query principal del listado ni su paginación.
 *
 * IMPORTANTE: los valores de texto se escapan con e() porque el wrapper
 * Vuetable renderiza los campos comunes con v-html, y tanto las respuestas
 * como los valores de seguimiento los escriben usuarios.
 */
class EnriquecedorFilas
{
    /**
     * @param Collection $filas    filas de la página (modelos Eloquent)
     * @param string     $listKey  key del registry (config/listados.php)
     * @param int        $contextId
     * @param int|null   $idActividad para inyectar respuestas de preguntas (solo inscripciones)
     * @param string     $recordKey  atributo de la fila con la PK del registro
     */
    public function enriquecer(Collection $filas, string $listKey, $contextId, $idActividad = null, $recordKey = 'id')
    {
        if ($filas->isEmpty()) {
            return $filas;
        }

        $ids = $filas->pluck($recordKey);

        if ($idActividad !== null) {
            $this->inyectarRespuestas($filas, $ids, $idActividad, $recordKey);
        }

        $this->inyectarValoresSeguimiento($filas, $ids, $listKey, $contextId, $recordKey);

        return $filas;
    }

    /**
     * Métricas del voluntario por persona (historial completo), para las
     * columnas opcionales de inscripciones. Dos queries agregadas sobre las
     * personas de la página (sin N+1):
     *  - participaciones: cantidad de actividades donde estuvo PRESENTE
     *    (definición canónica: participación = presencia). El "nivel"
     *    (Rookie/Champion/Guardian) lo deriva la celda desde este número.
     *  - evaluacion_general: promedio histórico de los puntajes de competencia
     *    que la persona RECIBIÓ (evaluacion_persona_respuestas.score).
     */
    public function inyectarMetricasVoluntario(Collection $filas, $personaKey = 'idPersona')
    {
        if ($filas->isEmpty()) {
            return $filas;
        }

        $idsPersona = $filas->pluck($personaKey)->filter()->unique();
        if ($idsPersona->isEmpty()) {
            return $filas;
        }

        $participaciones = DB::table('Inscripcion')
            ->select('idPersona', DB::raw('COUNT(*) as total'))
            ->where('presente', 1)
            ->whereNull('deleted_at')
            ->whereIn('idPersona', $idsPersona)
            ->groupBy('idPersona')
            ->pluck('total', 'idPersona');

        $evaluaciones = DB::table('EvaluacionPersona')
            ->join('evaluacion_persona_respuestas', 'evaluacion_persona_respuestas.idEvaluacionPersona', '=', 'EvaluacionPersona.idEvaluacionPersona')
            ->select('EvaluacionPersona.idEvaluado', DB::raw('AVG(evaluacion_persona_respuestas.score) as promedio'))
            ->whereIn('EvaluacionPersona.idEvaluado', $idsPersona)
            ->whereNotNull('evaluacion_persona_respuestas.score')
            ->groupBy('EvaluacionPersona.idEvaluado')
            ->pluck('promedio', 'idEvaluado');

        foreach ($filas as $fila) {
            $idPersona = $fila->{$personaKey};
            $fila->setAttribute('participaciones', (int) $participaciones->get($idPersona, 0));
            $promedio = $evaluaciones->get($idPersona);
            $fila->setAttribute('evaluacion_general', $promedio !== null ? round($promedio, 1) : null);
        }

        return $filas;
    }

    private function inyectarRespuestas(Collection $filas, Collection $ids, $idActividad, $recordKey)
    {
        $preguntas = ActividadPregunta::where('actividad_id', $idActividad)->get(['id', 'tipo']);
        if ($preguntas->isEmpty()) {
            return;
        }

        $tipos = $preguntas->pluck('tipo', 'id');

        $respuestas = InscripcionRespuesta::whereIn('inscripcion_id', $ids)
            ->whereIn('pregunta_id', $preguntas->pluck('id'))
            ->get()
            ->groupBy('inscripcion_id');

        foreach ($filas as $fila) {
            foreach ($respuestas->get($fila->{$recordKey}, collect()) as $respuesta) {
                // Las preguntas tipo 'archivo' guardan el path privado del archivo:
                // se renderiza como link a la descarga autenticada, no como texto.
                if ($tipos->get($respuesta->pregunta_id) === 'archivo') {
                    $valor = $respuesta->respuesta
                        ? '<a href="' . e(url('/admin/actividades/' . $idActividad . '/respuesta/' . $respuesta->id . '/archivo'))
                            . '" target="_blank" rel="noopener">' . e(__('backend.ver_archivo')) . '</a>'
                        : '';
                } else {
                    $valor = e($respuesta->respuesta);
                }

                $fila->setAttribute('pregunta_' . $respuesta->pregunta_id, $valor);
            }
        }
    }

    private function inyectarValoresSeguimiento(Collection $filas, Collection $ids, $listKey, $contextId, $recordKey)
    {
        $columnas = ListadoColumna::where('list_key', $listKey)
            ->where('context_id', $contextId)
            ->get();
        if ($columnas->isEmpty()) {
            return;
        }

        $valores = ListadoColumnaValor::whereIn('columna_id', $columnas->pluck('id'))
            ->whereIn('record_id', $ids)
            ->get();

        // Las columnas tipo persona guardan idPersona: se resuelve a nombre en un solo query.
        $idsPersona = $valores->whereIn('columna_id', $columnas->where('tipo', 'persona')->pluck('id'))
            ->pluck('valor')
            ->unique()
            ->filter();
        $personas = $idsPersona->isEmpty()
            ? collect()
            : Persona::whereIn('idPersona', $idsPersona)
                ->get(['idPersona', 'nombres', 'apellidoPaterno'])
                ->keyBy('idPersona');

        $porRegistro = $valores->groupBy('record_id');
        $tipos = $columnas->pluck('tipo', 'id');

        foreach ($filas as $fila) {
            foreach ($porRegistro->get($fila->{$recordKey}, collect()) as $valor) {
                $fila->setAttribute(
                    'custom_' . $valor->columna_id,
                    $this->presentar($tipos->get($valor->columna_id), $valor->valor, $personas)
                );
            }
        }
    }

    /**
     * Forma en que la celda Vue recibe el valor según el tipo. A diferencia de
     * las respuestas a preguntas (campos comunes, renderizados con v-html),
     * estos valores solo se muestran dentro del componente celda-seguimiento
     * con interpolación de Vue, que ya escapa — no se aplica e() para no
     * double-escapar.
     */
    private function presentar($tipo, $valor, Collection $personas)
    {
        switch ($tipo) {
            case 'etiquetas':
                return json_decode($valor, true) ?: [];
            case 'persona':
                $persona = $personas->get($valor);
                return [
                    'id' => (int) $valor,
                    'nombre' => $persona ? trim($persona->nombres . ' ' . $persona->apellidoPaterno) : '',
                ];
            default: // casilla, fecha, texto, estado
                return $valor;
        }
    }
}
