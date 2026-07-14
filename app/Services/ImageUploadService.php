<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
