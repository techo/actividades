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
                    // El valor lo escapa/arma EnriquecedorFilas server-side (texto con e()
                    // o link de archivo): se renderiza como HTML confiable, sin re-escapar.
                    'html' => true,
                ];
            })
            ->all();
    }

}
