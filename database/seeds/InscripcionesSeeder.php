<?php

use Illuminate\Database\Seeder;

class InscripcionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $p = factory(\App\Persona::class)->create();
        $a = factory(\App\Actividad::class)->create([
                'idCoordinador' => $p->idPersona,
                'idPersonaCreacion' => $p->idPersona,
                'idPersonaModificacion' => $p->idPersona
            ]);

        $pe = factory(\App\PuntoEncuentro::class)->create([ 
            'idActividad' => $a->idActividad
        ]);

        $i = factory(\App\Inscripcion::class)
            ->create([
                'idActividad' => $a->idActividad,
                'idPuntoEncuentro' => $pe->idPuntoEncuentro
            ]);
        
    }
}