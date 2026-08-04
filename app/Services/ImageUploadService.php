<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;

/**
 * Guarda archivos subidos comprimiendo las imágenes rasterizables.
 *
 * Reemplaza a UploadedFile::store(): devuelve el mismo formato de path
 * (ej. "public/perfil/img/xxxx.jpg") para que el código consumidor
 * (transformación public<->storage, guardado en DB) no cambie.
 *
 * Las imágenes JPG/PNG/WEBP se redimensionan a MAX_DIM y se recomprimen;
 * los PDF, GIF (posible animación) y cualquier otro archivo se guardan tal cual.
 */
class ImageUploadService
{
    /** Extensiones rasterizables que comprimimos. */
    const IMAGE_EXTS = ['jpg', 'jpeg', 'png', 'webp'];

    /**
     * Allowlist de extensiones EFECTIVAS permitidas (derivadas del contenido del archivo).
     * Backstop central de seguridad: cierra la subida de archivos arbitrarios (RCE / XSS)
     * para TODOS los consumidores de este servicio, no solo los que validan en el request.
     *
     * Nota: UploadedFile::store() nombra el archivo con la extensión que devuelve
     * guessExtension() (basada en el MIME real del contenido), no con la del nombre
     * original del cliente. Por eso validamos esa extensión efectiva: un .php con
     * contenido PHP se detecta como 'php' y se rechaza; un SVG como 'svg' y se rechaza.
     * Se incluye 'zip' porque los .docx/.xlsx/.pptx suelen detectarse como zip.
     */
    const ALLOWED_STORED_EXTS = [
        'jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp',        // imágenes (svg excluido a propósito: vector XSS)
        'pdf',                                             // documentos PDF
        'zip', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',// ofimática
        'txt', 'csv',                                      // texto plano
    ];

    /** Tamaño máximo permitido por archivo (10 MB). */
    const MAX_BYTES = 10485760;

    /**
     * Valida que el archivo sea seguro de almacenar. Lanza ValidationException (HTTP 422)
     * en caso contrario. Es el único punto que garantiza la validación aunque el endpoint
     * llamador se olvide de restringir mimes/size.
     */
    private static function assertSafe(UploadedFile $file)
    {
        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                'archivo' => ['El archivo subido no es válido.'],
            ]);
        }

        if ($file->getSize() !== null && $file->getSize() > self::MAX_BYTES) {
            throw ValidationException::withMessages([
                'archivo' => ['El archivo supera el tamaño máximo permitido (10 MB).'],
            ]);
        }

        // Extensión con la que efectivamente se persiste el archivo (según su contenido).
        $stored = strtolower((string) $file->guessExtension());
        if ($stored !== '' && ! in_array($stored, self::ALLOWED_STORED_EXTS, true)) {
            throw ValidationException::withMessages([
                'archivo' => ['Tipo de archivo no permitido.'],
            ]);
        }
    }

    /** Lado máximo en píxeles: las imágenes más grandes se redimensionan (sin agrandar). */
    const MAX_DIM = 1600;

    /** Calidad de compresión JPG (0-100). */
    const QUALITY = 80;

    /**
     * Guarda un archivo subido, comprimiéndolo si es una imagen.
     *
     * @param  UploadedFile  $file  archivo del request
     * @param  string        $dir   directorio destino (ej. "public/perfil/img")
     * @return string               path relativo en el disco default (ej. "public/perfil/img/xxxx.jpg")
     */
    public static function store(UploadedFile $file, string $dir)
    {
        // Backstop de seguridad: rechaza tipos no permitidos y archivos demasiado grandes
        // antes de tocar el disco.
        self::assertSafe($file);

        $dir = trim($dir, '/');
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension());

        // No es imagen rasterizable (PDF, GIF animado, etc.): se guarda sin tocar.
        if (! in_array($ext, self::IMAGE_EXTS)) {
            return $file->store($dir);
        }

        try {
            $image = Image::make($file->getRealPath());
        } catch (\Exception $e) {
            // Si Intervention no puede procesarla, no rompemos el flujo: guardamos el original.
            return $file->store($dir);
        }

        // Corrige la orientación según metadata EXIF (fotos de celular rotadas).
        $image->orientate();

        // Redimensiona solo si excede el máximo, manteniendo proporción y sin agrandar.
        if ($image->width() > self::MAX_DIM || $image->height() > self::MAX_DIM) {
            $image->resize(self::MAX_DIM, self::MAX_DIM, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        // PNG conserva transparencia; el resto se recomprime como JPG.
        if ($ext === 'png') {
            $encoded  = $image->encode('png');
            $filename = Str::random(40) . '.png';
        } else {
            $encoded  = $image->encode('jpg', self::QUALITY);
            $filename = Str::random(40) . '.jpg';
        }

        $path = $dir . '/' . $filename;
        Storage::put($path, (string) $encoded);

        $image->destroy();

        return $path;
    }
}
