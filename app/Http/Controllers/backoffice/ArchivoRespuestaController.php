<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\Controller;
use App\InscripcionRespuesta;
use App\SuscribeRespuesta;
use Illuminate\Support\Facades\Storage;

/**
 * Descarga autenticada de los archivos subidos como respuesta a preguntas
 * tipo 'archivo'. Los archivos viven en un disco privado (fuera de public/),
 * así que esta es la única vía de acceso; la autorización la aplican las
 * rutas (can:ver sobre la actividad / role:admin para campañas) y acá se
 * valida además que la respuesta pertenezca al contexto pedido.
 *
 * Se sirven con un nombre genérico ('pregunta-{id}.{ext}'); no se conserva el
 * nombre original del archivo.
 */
class ArchivoRespuestaController extends Controller
{
    /** Descarga del archivo de una respuesta de inscripción a actividad. */
    public function inscripcion($idActividad, $respuestaId)
    {
        $respuesta = InscripcionRespuesta::with(['inscripcion', 'pregunta'])->findOrFail($respuestaId);

        // La respuesta debe pertenecer a la actividad de la ruta.
        abort_unless(
            $respuesta->inscripcion && (int) $respuesta->inscripcion->idActividad === (int) $idActividad,
            404
        );

        return $this->descargar($respuesta);
    }

    /** Descarga del archivo de una respuesta de suscripción a campaña. */
    public function campana($campaignId, $respuestaId)
    {
        $respuesta = SuscribeRespuesta::with(['suscripcion', 'pregunta'])->findOrFail($respuestaId);

        // La respuesta debe pertenecer a la campaña de la ruta.
        abort_unless(
            $respuesta->suscripcion && (int) $respuesta->suscripcion->campaign_id === (int) $campaignId,
            404
        );

        return $this->descargar($respuesta);
    }

    /**
     * Valida que sea una respuesta tipo archivo con un archivo existente y lo
     * envía como descarga con nombre genérico.
     */
    private function descargar($respuesta)
    {
        $pregunta = $respuesta->pregunta;
        $path     = $respuesta->respuesta;

        abort_unless($pregunta && $pregunta->tipo === 'archivo', 404);
        abort_unless($path && Storage::exists($path), 404);

        $ext    = pathinfo($path, PATHINFO_EXTENSION);
        $nombre = 'pregunta-' . $pregunta->id . ($ext ? '.' . $ext : '');

        return Storage::download($path, $nombre);
    }
}
