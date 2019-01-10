 <?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OficinasArgentinaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('atl_oficinas')->insert([
            ['nombre' => 'Nacional Argentina'],
            ['nombre' => 'Regional Buenos Aires'],
            ['nombre' => 'Buenos Aires Norte'],
            ['nombre' => 'Buenos Aires Sur'],
            ['nombre' => 'Buenos Aires Oeste'],
            ['nombre' => 'Buenos Aires La Plata'],
            ['nombre' => 'Neuquén - Río Negro'],
            ['nombre' => 'Córdoba Capital'],
            ['nombre' => 'Río Cuarto'],
            ['nombre' => 'Rosario'],
            ['nombre' => 'Posadas'],
            ['nombre' => 'Oberá'],
            ['nombre' => 'Corrientes - Chaco'],
            ['nombre' => 'Resistencia'],
            ['nombre' => 'Salta'],
            ['nombre' => 'Tucumán']
        ]);

    }
}