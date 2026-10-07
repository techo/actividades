<?php

namespace App\Console\Commands;

use App\Novedad;
use Illuminate\Console\Command;

/**
 * Administra la barra de novedades del backoffice sin ABM: las novedades se redactan
 * charlando y se cargan con este comando (en prod, como www-data).
 *
 *   php artisan novedades                                  → lista
 *   php artisan novedades agregar "Arreglamos X 🙌" --link=https://...
 *   php artisan novedades agregar "Arreglamos X" --es_AR="Arreglamos X, ¡probalo!" --pt="Corrigimos X" --en="We fixed X"
 *   php artisan novedades agregar "Ahora podés pagar con PIX" --pt="Agora dá para pagar com PIX" --solo=pt
 *   php artisan novedades desactivar --id=12
 *   php artisan novedades activar --id=12
 */
class Novedades extends Command
{
    protected $signature = 'novedades
        {accion=listar : listar | agregar | desactivar | activar}
        {texto? : texto base de la novedad (fallback de cualquier idioma, máx. 255)}
        {--es_AR= : texto para Argentina (voseo)}
        {--es_CH= : texto para Chile y resto de LatAm}
        {--es= : texto en español genérico}
        {--pt= : texto en portugués}
        {--en= : texto en inglés}
        {--solo= : mostrar solo en estos locales, separados por coma (ej. pt o es_AR,es_CH)}
        {--link= : link opcional de "Más info"}
        {--id= : id de la novedad (para activar/desactivar)}';

    protected $description = 'Lista, agrega o (des)activa las novedades que rotan en la barra del backoffice.';

    public function handle()
    {
        switch ($this->argument('accion')) {
            case 'listar':
                return $this->listar();
            case 'agregar':
                return $this->agregar();
            case 'desactivar':
                return $this->cambiarActiva(false);
            case 'activar':
                return $this->cambiarActiva(true);
        }

        $this->error('Acción desconocida. Usá: listar | agregar | desactivar | activar.');
        return 1;
    }

    const LOCALES = ['es_AR', 'es_CH', 'es', 'pt', 'en'];

    private function listar()
    {
        $filas = Novedad::orderByDesc('activa')->orderByDesc('created_at')->get()
            ->map(function ($n) {
                return [
                    $n->id, $n->activa ? 'sí' : 'no', $n->created_at, $n->texto,
                    implode(',', array_keys($n->traducciones ?: [])),
                    $n->locales ? implode(',', $n->locales) : 'todos',
                    $n->link,
                ];
            });

        $this->table(['id', 'activa', 'creada', 'texto base', 'traducciones', 'se muestra a', 'link'], $filas);
        return 0;
    }

    private function agregar()
    {
        $texto = trim((string) $this->argument('texto'));
        $link = $this->option('link') ?: null;

        if ($texto === '' || mb_strlen($texto) > 255) {
            $this->error('El texto es obligatorio y no puede superar 255 caracteres.');
            return 1;
        }

        $traducciones = [];
        foreach (self::LOCALES as $locale) {
            $traduccion = trim((string) $this->option($locale));
            if ($traduccion === '') {
                continue;
            }
            if (mb_strlen($traduccion) > 255) {
                $this->error("El texto de --{$locale} no puede superar 255 caracteres.");
                return 1;
            }
            $traducciones[$locale] = $traduccion;
        }

        $solo = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('solo')))));
        foreach ($solo as $locale) {
            if (!in_array($locale, self::LOCALES)) {
                $this->error("Locale desconocido en --solo: {$locale}. Válidos: " . implode(', ', self::LOCALES));
                return 1;
            }
        }
        if ($link && !filter_var($link, FILTER_VALIDATE_URL)) {
            $this->error('El link no es una URL válida.');
            return 1;
        }

        $n = Novedad::create([
            'texto' => $texto,
            'traducciones' => $traducciones ?: null,
            'locales' => $solo ?: null,
            'link' => $link,
            'activa' => true,
        ]);
        $this->info("Novedad #{$n->id} creada y activa.");
        return 0;
    }

    private function cambiarActiva($activa)
    {
        $n = Novedad::find($this->option('id'));
        if (!$n) {
            $this->error('No existe una novedad con ese --id.');
            return 1;
        }

        $n->activa = $activa;
        $n->save();
        $this->info("Novedad #{$n->id} " . ($activa ? 'activada' : 'desactivada') . '.');
        return 0;
    }
}
