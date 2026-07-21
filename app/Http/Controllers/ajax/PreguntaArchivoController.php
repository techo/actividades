<?php

namespace App\Http\Controllers\ajax;

use App\ActividadPregunta;
use App\CampaignPregunta;
use App\Http\Controllers\Controller;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;

/**
 * Sube el archivo de una respuesta a una pregunta tipo 'archivo' (enfoque
 * asíncrono): el front lo sube al elegirlo y guarda el `path` devuelto como
 * valor de la respuesta. Así el submit del formulario sigue siendo el mismo
 * JSON de texto de siempre.
 *
 * Los archivos se guardan en el disco default (local) bajo un directorio
 * PRIVADO (fuera de `public/`), por lo que NO son accesibles por URL: la
 * descarga se sirve por rutas autenticadas del backoffice.
 */
class PreguntaArchivoController extends Controller
{
    /** Directorio privado (relativo a storage/app), fuera de public/. */
    const DIR = 'respuestas_archivos';

    /** Tamaño máximo en KB (5 MB). */
    const MAX_KB = 5120;

    /** Extensiones permitidas: imagen o PDF. */
    const MIMES = 'jpg,jpeg,png,pdf';

    /**
     * Upload para inscripción a actividad (voluntario autenticado; la ruta
     * ya exige auth).
     */
    public function inscripcion(Request $request)
    {
        $pregunta = ActividadPregunta::findOrFail($request->input('pregunta_id'));

        return $this->guardar($request, $pregunta);
    }

    /**
     * Upload para suscripción a campaña (endpoint público, igual que el
     * submit de suscripción anónima).
     */
    public function campana(Request $request)
    {
        $pregunta = CampaignPregunta::findOrFail($request->input('pregunta_id'));

        return $this->guardar($request, $pregunta);
    }

    private function guardar(Request $request, $pregunta)
    {
        // Solo preguntas tipo 'archivo' aceptan uploads.
        if ($pregunta->tipo !== 'archivo') {
            abort(422, 'La pregunta no admite archivos.');
        }

        $request->validate([
            'archivo' => 'required|file|mimes:' . self::MIMES . '|max:' . self::MAX_KB,
        ]);

        $path = ImageUploadService::store($request->file('archivo'), self::DIR);

        return response()->json([
            'path'   => $path,
            'nombre' => $request->file('archivo')->getClientOriginalName(),
        ]);
    }
}
