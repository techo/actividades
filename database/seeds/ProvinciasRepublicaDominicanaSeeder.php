<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinciasRepublicaDominicanaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pais = App\Pais::where('nombre', 'República Dominicana')->first();

        $listado_provincias = [
            'Azua',
            'Bahoruco',
            'Barahona',
            'Dajabón',
            'Distrito Nacional',
            'Duarte',
            'Elías Piña',
            'El Seibo',
            'Espaillat',
            'Hato Mayor',
            'Hermanas Mirabal',
            'Independencia',
            'La Altagracia',
            'La Romana',
            'La Vega',
            'María Trinidad Sánchez',
            'Monseñor Nouel',
            'Monte Cristi',
            'Monte Plata',
            'Pedernales',
            'Peravia',
            'Puerto Plata',
            'Samaná',
            'San Cristóbal',
            'San José de Ocoa',
            'San Juan',
            'San Pedro de Macorís',
            'Sánchez Ramírez',
            'Santiago',
            'Santiago Rodríguez',
            'Santo Domingo',
            'Valverde'
        ];

        foreach ($listado_provincias as $v) {
            $p = new App\Provincia;
            $p->provincia = $v;
            $p->id_pais = $pais->id;
            $p->save();
        }

    }
}