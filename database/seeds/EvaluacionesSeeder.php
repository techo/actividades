<?php

use Illuminate\Database\Seeder;

class EvaluacionesSeeder extends Seeder
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

        $i = factory(\App\Inscripcion::class,10)
            ->create([
                'idActividad' => $a->idActividad,
                'idPuntoEncuentro' => $pe->idPuntoEncuentro,
                'presente' => 1
            ])
            ->each(function ($i){
                factory(\App\EvaluacionActividad::class)->create([
                    'idActividad' => $i->idActividad,
                    'idPersona' => $i->idPersona
                ]);
                factory(\App\EvaluacionPersona::class,2)->create([
                    'idActividad' => $i->idActividad,
                    'idEvaluador' => $i->idPersona,
                    'idEvaluado' => $i->idPersona,
                ]);
            });
        
    }
}