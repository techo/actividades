<?php

use Illuminate\Database\Seeder;

class ActividadesConPagoSeeder extends Seeder
{
    /**
     * Actividades que requieren pago (y confirmación) para probar esos flujos.
     */
    public function run()
    {
        factory('App\Actividad')
            ->states('con pago')
            ->create([
                'ficha_medica_campos' => [],
                'roles_tags' => [],
                'actividades_tags' => [],
                'tipo_inscriptos_tag' => [],
                'costo' => '100.00',
            ])
            ->puntosEncuentro()->save(factory(\App\PuntoEncuentro::class)->make());

        factory('App\Actividad')
            ->states('con confirmacion y pago')
            ->create([
                'ficha_medica_campos' => [],
                'roles_tags' => [],
                'actividades_tags' => [],
                'tipo_inscriptos_tag' => [],
                'costo' => '100.00',
            ])
            ->puntosEncuentro()->save(factory(\App\PuntoEncuentro::class)->make());
    }
}
