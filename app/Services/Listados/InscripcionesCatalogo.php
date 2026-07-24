<?php

namespace App\Services\Listados;

use App\Actividad;
use App\ActividadPregunta;

/**
 * Catálogo de campos del listado de Inscripciones de una actividad.
 * context_id = idActividad. Todos los campos referencian datos que ya vienen
 * en el payload de InscripcionesSearch o que inyecta EnriquecedorFilas.
 */
class InscripcionesCatalogo implements CatalogoListado
{
    use ColumnasSeguimiento;

    const LIST_KEY = 'inscripciones';

    public function config($contextId): array
    {
        $actividad = Actividad::findOrFail($contextId);

        $grupos = [
            [
                'key' => 'datos_generales',
                'titulo' => 'backend.general_data',
                'campos' => $this->datosGenerales($actividad),
            ],
        ];

        $preguntas = $this->preguntas($contextId);
        if (count($preguntas)) {
            $grupos[] = [
                'key' => 'preguntas',
                'titulo' => 'backend.additional_questions',
                'campos' => $preguntas,
            ];
        }

        $grupos[] = [
            'key' => 'ficha_medica',
            'titulo' => 'backend.medical_record',
            'campos' => config('datatables.inscripciones.catalogo.ficha_medica'),
        ];

        $grupos[] = [
            'key' => 'seguimiento',
            'titulo' => 'backend.tracking',
            'campos' => $this->camposSeguimiento(static::LIST_KEY, $contextId),
        ];

        return [
            'fijas' => config('datatables.inscripciones.fijas'),
            'grupos' => $grupos,
            'defaults' => $this->defaults($actividad),
        ];
    }

    public function defaultFields($contextId): array
    {
        $actividad = Actividad::findOrFail($contextId);

        $porKey = collect($this->datosGenerales($actividad))->keyBy('key');

        $defaults = collect($this->defaults($actividad))
            ->map(function ($key) use ($porKey) {
                return $porKey->get($key);
            })
            ->filter()
            ->values()
            ->all();

        return array_merge(config('datatables.inscripciones.fijas'), $defaults);
    }

    private function datosGenerales(Actividad $actividad): array
    {
        $campos = config('datatables.inscripciones.catalogo.datos_generales');

        // Confirmación y pago solo existen como columna si la actividad los usa.
        if ($actividad->confirmacion == 1) {
            $campos[] = [
                'key' => 'confirma',
                'name' => '__component:confirma',
                'title' => 'Confirma',
                'titleClass' => 'text-center',
                'dataClass' => 'text-center',
            ];
        }
        if ($actividad->pago == 1) {
            $campos[] = [
                'key' => 'pago',
                'name' => '__component:pago',
                'title' => 'Pago',
                'titleClass' => 'text-center',
                'dataClass' => 'text-center',
            ];
            // Comprobante de pago: solo tiene sentido si la actividad cobra.
            $campos[] = [
                'key' => 'voucher',
                'name' => '__component:celda-voucher',
                'title' => 'backend.voucher_column',
            ];
        }

        // Beca/exención: solo si la actividad la habilita (permite_exencion).
        if ($actividad->permite_exencion) {
            $campos[] = [
                'key' => 'beca',
                'name' => '__component:celda-beca',
                'title' => 'backend.scholarship_column',
            ];
        }

        return $campos;
    }

    private function defaults(Actividad $actividad): array
    {
        $defaults = config('datatables.inscripciones.defaults');

        // Mantiene el comportamiento histórico: confirma/pago visibles antes
        // de la columna de asistencia cuando la actividad los requiere.
        $condicionales = [];
        if ($actividad->confirmacion == 1) {
            $condicionales[] = 'confirma';
        }
        if ($actividad->pago == 1) {
            $condicionales[] = 'pago';
        }

        if ($condicionales) {
            $pos = array_search('asistencia', $defaults);
            $pos = $pos === false ? count($defaults) : $pos;
            array_splice($defaults, $pos, 0, $condicionales);
        }

        return $defaults;
    }

    private function preguntas($idActividad): array
    {
        return ActividadPregunta::where('actividad_id', $idActividad)
            ->orderBy('orden')
            ->get()
            ->map(function ($pregunta) {
                return [
                    'key' => 'pregunta_' . $pregunta->id,
                    'name' => 'pregunta_' . $pregunta->id,
                    'title' => $pregunta->pregunta,
                ];
            })
            ->all();
    }

    /**
     * Campos filtrables: base (columnas/expresiones reales del SELECT de
     * InscripcionesSearch) + preguntas consultables + columnas de seguimiento.
     * Deliberadamente EXCLUYE los agregados post-paginación (participaciones,
     * nivel, evaluacion_general) y las columnas de presentación (whatsapp,
     * tipoInscripcion, documento, voucher, beca).
     */
    public function filterableFields($contextId): array
    {
        $actividad = Actividad::findOrFail($contextId);

        $base = [
            'dni'              => ['type' => 'text', 'sql' => 'Persona.dni'],
            'telefonoMovil'    => ['type' => 'text', 'sql' => 'Persona.telefonoMovil'],
            'mail'             => ['type' => 'text', 'sql' => 'Persona.mail'],
            'edad'             => ['type' => 'number', 'sql' => '__edad'],
            'genero'           => ['type' => 'text', 'sql' => 'Persona.genero'],
            'pPais'            => ['type' => 'text', 'sql' => 'personaPais.nombre'],
            'pProvincia'       => ['type' => 'text', 'sql' => 'personaProvincia.provincia'],
            'pLocalidad'       => ['type' => 'text', 'sql' => 'personaLocalidad.localidad'],
            'oficina'          => ['type' => 'text', 'sql' => 'oficinaPersona.nombre'],
            'fechaInscripcion' => ['type' => 'date', 'sql' => 'Inscripcion.fechaInscripcion'],
            'punto'            => ['type' => 'text', 'sql' => 'PuntoEncuentro.punto'],
            'jornadas'         => ['type' => 'text', 'sql' => '__subquery:jornadas'],
            'nombreGrupo'      => ['type' => 'text', 'sql' => '__subquery:nombreGrupo'],
            'estado_persona'   => ['type' => 'text', 'sql' => 'Persona.estadoPersona'],
            'asistencia'       => ['type' => 'bool', 'sql' => 'Inscripcion.presente'],
        ];

        // Los mismos condicionales que datosGenerales(): solo si la actividad los usa.
        if ($actividad->confirmacion == 1) {
            $base['confirma'] = ['type' => 'bool', 'sql' => 'Inscripcion.confirma'];
        }
        if ($actividad->pago == 1) {
            $base['pago'] = ['type' => 'bool', 'sql' => 'Inscripcion.pago'];
        }

        // Ficha médica (join `ficha`): mismos campos que ofrece el grupo homónimo
        // del panel de columnas. Texto libre; el 'documento' es un archivo, no filtra.
        $fichaMedica = [
            'grupo_sanguinieo'  => ['type' => 'text', 'sql' => 'ficha.grupo_sanguinieo'],
            'cobertura_nombre'  => ['type' => 'text', 'sql' => 'ficha.cobertura_nombre'],
            'cobertura_numero'  => ['type' => 'text', 'sql' => 'ficha.cobertura_numero'],
            'contacto_nombre'   => ['type' => 'text', 'sql' => 'ficha.contacto_nombre'],
            'contacto_telefono' => ['type' => 'text', 'sql' => 'ficha.contacto_telefono'],
            'contacto_relacion' => ['type' => 'text', 'sql' => 'ficha.contacto_relacion'],
            'alergias'          => ['type' => 'text', 'sql' => 'ficha.alergias'],
            'vacunacion_covid'  => ['type' => 'text', 'sql' => 'ficha.vacunacion_covid'],
            'alimentacion'      => ['type' => 'text', 'sql' => 'ficha.alimentacion'],
        ];

        return array_merge(
            $base,
            $fichaMedica,
            $this->preguntasFiltrables($contextId),
            $this->camposSeguimientoFiltrables(static::LIST_KEY, $contextId)
        );
    }

    public function groupableFields($contextId): array
    {
        // 'jornadas' queda fuera: es multivaluado (GROUP_CONCAT), no agrupa por
        // valor único. 'estado' canónico se difiere (lógica PHP en EstadoInscripcion).
        $base = [
            'genero', 'pPais', 'pProvincia', 'pLocalidad', 'oficina',
            'punto', 'nombreGrupo', 'estado_persona', 'asistencia',
        ];

        $actividad = Actividad::findOrFail($contextId);
        if ($actividad->confirmacion == 1) {
            $base[] = 'confirma';
        }
        if ($actividad->pago == 1) {
            $base[] = 'pago';
        }

        return array_merge(
            $base,
            $this->preguntasAgrupables($contextId),
            $this->camposSeguimientoAgrupables(static::LIST_KEY, $contextId)
        );
    }

    /**
     * Vistas predefinidas. Solo referencian campos base garantizados y
     * consultables en SQL (nunca agregados post-paginación ni columnas de
     * seguimiento, que dependen del contexto).
     */
    public function defaultViews($contextId): array
    {
        $vistas = [
            [
                'nombre' => 'backend.vista_todos',
                'color' => '#1f6feb',
                'config' => ['filtros' => [], 'group_by' => null],
            ],
        ];

        $actividad = Actividad::findOrFail($contextId);
        if ($actividad->confirmacion == 1) {
            $vistas[] = [
                'nombre' => 'backend.vista_confirmados',
                'color' => '#2ea043',
                'config' => [
                    'filtros' => [['campo' => 'confirma', 'condicion' => '=', 'valor' => 1]],
                    'group_by' => null,
                ],
            ];
        }

        return $vistas;
    }

    /**
     * Preguntas filtrables: 'abierta' → texto, 'desplegable' → enum (con opciones).
     * 'archivo' se excluye (es un link a descarga, no un valor comparable).
     */
    private function preguntasFiltrables($idActividad): array
    {
        return ActividadPregunta::where('actividad_id', $idActividad)
            ->whereIn('tipo', ['abierta', 'desplegable'])
            ->orderBy('orden')
            ->get()
            ->mapWithKeys(function ($pregunta) {
                $descriptor = [
                    'type' => $pregunta->tipo === 'desplegable' ? 'enum' : 'text',
                    'sql' => '__pregunta:' . $pregunta->id,
                ];
                if ($pregunta->tipo === 'desplegable') {
                    $descriptor['opciones'] = $pregunta->opciones;
                }
                return ['pregunta_' . $pregunta->id => $descriptor];
            })
            ->all();
    }

    private function preguntasAgrupables($idActividad): array
    {
        return ActividadPregunta::where('actividad_id', $idActividad)
            ->where('tipo', 'desplegable')
            ->orderBy('orden')
            ->get()
            ->map(function ($pregunta) {
                return 'pregunta_' . $pregunta->id;
            })
            ->values()
            ->all();
    }

}
