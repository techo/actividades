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
 *   php artisan novedades desactivar --id=12
 *   php artisan novedades activar --id=12
 */
class Novedades extends Command
{
    protected $signature = 'novedades
        {accion=listar : listar | agregar | desactivar | activar}
        {texto? : texto de la novedad (para agregar, máx. 255)}
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

    private function listar()
    {
        $filas = Novedad::orderByDesc('activa')->orderByDesc('created_at')->get()
            ->map(function ($n) {
                return [$n->id, $n->activa ? 'sí' : 'no', $n->created_at, $n->texto, $n->link];
            });

        $this->table(['id', 'activa', 'creada', 'texto', 'link'], $filas);
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
        if ($link && !filter_var($link, FILTER_VALIDATE_URL)) {
            $this->error('El link no es una URL válida.');
            return 1;
        }

        $n = Novedad::create(['texto' => $texto, 'link' => $link, 'activa' => true]);
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
