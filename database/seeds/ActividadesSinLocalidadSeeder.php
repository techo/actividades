<?php

use Illuminate\Database\Seeder;

class ActividadesSinLocalidadSeeder extends Seeder
{
    /**
     * Actividad sin localidad (caso borde de datos geográficos;
     * idProvincia es NOT NULL en el schema, solo la localidad puede faltar).
     */
    public function run()
    {
        factory('App\Actividad')
            ->create([
                'ficha_medica_campos' => [],
                'roles_tags' => [],
                'actividades_tags' => [],
                'tipo_inscriptos_tag' => [],
                'idLocalidad' => null,
            ])
            ->puntosEncuentro()->save(factory(\App\PuntoEncuentro::class)->make());
    }
}
