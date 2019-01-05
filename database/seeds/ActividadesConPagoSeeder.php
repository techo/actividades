<?php

use Illuminate\Database\Seeder;

class ActividadesConPagoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pais = factory(\App\Pais::class)->create(['config_pago' => "{\"merchant_id\": \"XX\",\"account_id\": \"XX\",\"api_key\": \"XX\",\"payment_class\": \"PayU\"}"]);
        
        $provincia = factory(\App\Provincia::class)->create([
            'id_pais' => $pais->id
        ]);

        factory(\App\Actividad::class,5)
            ->create([
                'idTipo' => factory(\App\Tipo::class)->create(['flujo' => 'CONSTRUCCION'])->idTipo,
                'idProvincia' => $provincia->id,
                'idPais' => $pais->id
            ])
            ->each(function ($a) { 
                $a->puntosEncuentro()->save(factory(\App\PuntoEncuentro::class)->make(
                    [
                        'idProvincia' => $a->idProvincia
                    ]));
            });
    }
}

