<?php

namespace App\Console\Commands;

use App\Persona;
use Illuminate\Console\Command;

/**
 * Aplica Persona::cortarAcceso() a las cuentas que ya se dieron de baja (anonimizadas)
 * antes del fix de la task 50 (reclamo #15): seguían autenticando por "recordarme" o por
 * tokens de la app en otros dispositivos.
 *
 * DRY-RUN por defecto. Con --commit escribe. Idempotente.
 */
class CortarAccesoAnonimizadas extends Command
{
    protected $signature = 'personas:cortar-acceso-anonimizadas
        {--commit : escribir de verdad (por defecto es dry-run)}';

    protected $description = 'Invalida credenciales y tokens de las cuentas dadas de baja (anonimizadas). Dry-run por defecto.';

    public function handle()
    {
        $commit = (bool) $this->option('commit');

        // Se identifican por el mail anonimizado (Str::random, sin '@'), no por
        // estadoPersona: las bajas anteriores a sep-2026 no se marcaban 'Desvinculado'
        // (en prod: 98 con la marca vs. ~708 sin ella).
        $query = Persona::where('nombres', 'Usuario eliminado')
            ->where('mail', 'not like', '%@%');

        $total = (clone $query)->count();
        $this->info(($commit ? '' : '[DRY-RUN] ') . "Cuentas anonimizadas: {$total}.");

        if (!$commit) {
            return 0;
        }

        $query->chunkById(200, function ($personas) {
            foreach ($personas as $persona) {
                $persona->cortarAcceso();
                $persona->save();
            }
        }, 'idPersona');

        $this->info("Acceso cortado en {$total} cuentas.");
        return 0;
    }
}
