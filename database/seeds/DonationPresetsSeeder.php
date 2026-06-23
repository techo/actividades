<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Carga inicial de montos sugeridos de donación por país.
 *
 * Los montos los define PRODUCTO (no son tipo de cambio). Acá va solo lo
 * confirmado. Los países sin fila caen al default global USD 5/10/20 que
 * resuelve DonationController@checkoutConfig — así que esta tabla se completa
 * a medida que producto acuerda los montos de cada país.
 *
 * `id_pais` referencia atl_pais.id (ver PaisesSeeder). Ojo: el id_pais=142 del
 * ejemplo de la spec es ilustrativo; el de México real es 146.
 *
 * Para agregar un país: copiar una fila al array y poner sus valores en
 * UNIDAD MAYOR (34 = $34.00). minor_unit_exponent = decimales de la moneda
 * (2 normal, 0 para CLP/PYG). pix_enabled solo Brasil hoy.
 */
class DonationPresetsSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        $presets = [
            // México — confirmado por producto.
            [
                'id_pais'             => 146,
                'currency'            => 'mxn',
                'preset_low'          => 34,
                'preset_mid'          => 85,
                'preset_high'         => 170,
                'minor_unit_exponent' => 2,
                'pix_enabled'         => false,
            ],

            // TODO producto: agregar el resto de los países operativos con sus
            // montos acordados. Mientras tanto caen al default USD 5/10/20.
            // Referencia de moneda/decimales (montos pendientes de definir):
            //   Argentina  id 13  ars  exp 2
            //   Brasil     id 33  brl  exp 2  pix_enabled = true
            //   Chile      id 46  clp  exp 0
            //   Colombia   id 52  cop  exp 2
            //   Perú       id ..  pen  exp 2
            //   Uruguay    id ..  uyu  exp 2
        ];

        foreach ($presets as $preset) {
            DB::table('donation_presets')->updateOrInsert(
                ['id_pais' => $preset['id_pais']],
                array_merge($preset, ['updated_at' => $now, 'created_at' => $now])
            );
        }
    }
}
