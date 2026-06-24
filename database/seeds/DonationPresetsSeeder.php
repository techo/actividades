<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Carga de montos sugeridos de donación por país (Amigos de TECHO).
 *
 * Los montos los define PRODUCTO (no son tipo de cambio) y van en UNIDAD MAYOR.
 * Una fila por país habilitado en prod. Los países sin fila caen al default
 * global USD 5/10/20 que resuelve DonationController@checkoutConfig.
 *
 * `id_pais` referencia atl_pais.id (ver PaisesSeeder).
 * minor_unit_exponent = decimales de la moneda en Stripe (2 normal; 0 para
 * monedas sin decimales: CLP, PYG). pix_enabled = true solo Brasil.
 *
 * Quedan fuera (no son países habilitados): Haití (HTG) y Europa (EUR).
 * Latam (id 241) no tiene montos definidos → cae al default USD 5/10/20.
 */
class DonationPresetsSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        $presets = [
            ['id_pais' => 13,  'currency' => 'ars', 'preset_low' => 2900,  'preset_mid' => 7000,  'preset_high' => 14000, 'minor_unit_exponent' => 2, 'pix_enabled' => false], // Argentina
            ['id_pais' => 29,  'currency' => 'bob', 'preset_low' => 14,    'preset_mid' => 35,    'preset_high' => 70,    'minor_unit_exponent' => 2, 'pix_enabled' => false], // Bolivia
            ['id_pais' => 33,  'currency' => 'brl', 'preset_low' => 10,    'preset_mid' => 25,    'preset_high' => 50,    'minor_unit_exponent' => 2, 'pix_enabled' => true],  // Brasil
            ['id_pais' => 46,  'currency' => 'clp', 'preset_low' => 1800,  'preset_mid' => 4600,  'preset_high' => 9000,  'minor_unit_exponent' => 0, 'pix_enabled' => false], // Chile
            ['id_pais' => 52,  'currency' => 'cop', 'preset_low' => 7000,  'preset_mid' => 18000, 'preset_high' => 36000, 'minor_unit_exponent' => 2, 'pix_enabled' => false], // Colombia
            ['id_pais' => 60,  'currency' => 'crc', 'preset_low' => 900,   'preset_mid' => 2300,  'preset_high' => 4600,  'minor_unit_exponent' => 2, 'pix_enabled' => false], // Costa Rica
            ['id_pais' => 65,  'currency' => 'dop', 'preset_low' => 120,   'preset_mid' => 290,   'preset_high' => 600,   'minor_unit_exponent' => 2, 'pix_enabled' => false], // República Dominicana
            ['id_pais' => 66,  'currency' => 'usd', 'preset_low' => 2,     'preset_mid' => 5,     'preset_high' => 10,    'minor_unit_exponent' => 2, 'pix_enabled' => false], // Ecuador
            ['id_pais' => 68,  'currency' => 'usd', 'preset_low' => 2,     'preset_mid' => 5,     'preset_high' => 10,    'minor_unit_exponent' => 2, 'pix_enabled' => false], // El Salvador
            ['id_pais' => 75,  'currency' => 'usd', 'preset_low' => 10,    'preset_mid' => 20,    'preset_high' => 50,    'minor_unit_exponent' => 2, 'pix_enabled' => false], // Estados Unidos
            ['id_pais' => 94,  'currency' => 'gtq', 'preset_low' => 15,    'preset_mid' => 40,    'preset_high' => 75,    'minor_unit_exponent' => 2, 'pix_enabled' => false], // Guatemala
            ['id_pais' => 102, 'currency' => 'hnl', 'preset_low' => 55,    'preset_mid' => 130,   'preset_high' => 270,   'minor_unit_exponent' => 2, 'pix_enabled' => false], // Honduras
            ['id_pais' => 146, 'currency' => 'mxn', 'preset_low' => 35,    'preset_mid' => 85,    'preset_high' => 170,   'minor_unit_exponent' => 2, 'pix_enabled' => false], // México
            ['id_pais' => 170, 'currency' => 'usd', 'preset_low' => 2,     'preset_mid' => 5,     'preset_high' => 10,    'minor_unit_exponent' => 2, 'pix_enabled' => false], // Panamá
            ['id_pais' => 172, 'currency' => 'pyg', 'preset_low' => 12000, 'preset_mid' => 31000, 'preset_high' => 62000, 'minor_unit_exponent' => 0, 'pix_enabled' => false], // Paraguay
            ['id_pais' => 173, 'currency' => 'pen', 'preset_low' => 7,     'preset_mid' => 17,    'preset_high' => 35,    'minor_unit_exponent' => 2, 'pix_enabled' => false], // Perú
            ['id_pais' => 229, 'currency' => 'uyu', 'preset_low' => 80,    'preset_mid' => 200,   'preset_high' => 410,   'minor_unit_exponent' => 2, 'pix_enabled' => false], // Uruguay
            ['id_pais' => 232, 'currency' => 'ves', 'preset_low' => 1200,  'preset_mid' => 2900,  'preset_high' => 6000,  'minor_unit_exponent' => 2, 'pix_enabled' => false], // Venezuela
        ];

        foreach ($presets as $preset) {
            DB::table('donation_presets')->updateOrInsert(
                ['id_pais' => $preset['id_pais']],
                array_merge($preset, ['updated_at' => $now, 'created_at' => $now])
            );
        }
    }
}
