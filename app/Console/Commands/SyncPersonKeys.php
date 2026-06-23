<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Asigna person_key (UUID) a las personas que todavía no lo tienen en
 * reporting_person. Se corre a diario (ver App\Console\Kernel) para que las
 * personas nuevas tengan su clave antes del refresh de Power BI.
 */
class SyncPersonKeys extends Command
{
    protected $signature = 'reporting:sync-person-keys';
    protected $description = 'Genera person_key (UUID) para personas nuevas en reporting_person.';

    public function handle()
    {
        $insertados = DB::statement('
            INSERT INTO reporting_person (idPersona, person_key, created_at)
            SELECT p.idPersona, UUID(), NOW()
            FROM Persona p
            WHERE NOT EXISTS (
                SELECT 1 FROM reporting_person rp WHERE rp.idPersona = p.idPersona
            )
        ');

        $faltantes = DB::table('Persona')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('reporting_person')
                  ->whereRaw('reporting_person.idPersona = Persona.idPersona');
            })
            ->count();

        $this->info('person_key sincronizado. Personas sin key restantes: ' . $faltantes);
    }
}
