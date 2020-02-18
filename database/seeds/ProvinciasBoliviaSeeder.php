<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinciasBoliviaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pais = App\Pais::where('nombre', 'Bolivia')->first();

        $listado_divisiones = [
            'Beni' =>
                [
                    'Cercado',
                    'Antonio Vaca Díez',
                    'General José Ballivián Segurola',
                    'Yacuma',
                    'Moxos',
                    'Marbán',
                    'Mamoré',
                    'Iténez',
                ],
            'Chuquisaca' => [
                'Oropeza',
                'Juana Azurduy de Padilla',
                'Jaime Zudáñez',
                'Tomina',
                'Hernando Siles',
                'Yamparáez',
                'Nor Cinti',
                'Sud Cinti',
                'Belisario Boeto',
                'Luis Calvo',
            ],
            'Cochabamba' => [
                'Arani',
                'Esteban Arce',
                'Arque',
                'Ayopaya',
                'Campero',
                'Capinota',
                'Cercado',
                'Carrasco',
                'Chapare',
                'Germán Jordán',
                'Mizque',
                'Punata',
                'Quillacollo',
                'Tapacarí',
                'Bolívar',
                'Tiraque',
            ],
            'La Paz' => [
                'Aroma',
                'Bautista Saavedra',
                'Abel Iturralde',
                'Caranavi',
                'Eliodoro Camacho',
                'Franz Tamayo',
                'Gualberto Villaroel',
                'Ingavi',
                'Inquisivi',
                'General José Manuel Pando',
                'Larecaja',
                'Loayza',
                'Los Andes',
                'Manco Kapac',
                'Muñecas',
                'Nor Yungas',
                'Omasuyos',
                'Pacajes',
                'Pedro Domingo Murillo',
                'Sud Yungas',
            ],
            'Oruro' => [
                'Sabaya',
                'Carangas',
                'Cercado',
                'Eduardo Abaroa',
                'Ladislao Cabrera',
                'Litoral',
                'Mejillones',
                'Nor Carangas',
                'Pantaleón Dalence',
                'Poopó',
                'Sajama',
                'San Pedro de Totora',
                'Saucarí',
                'Sebastián Pagador',
                'Sud Carangas',
                'Tomas Barrón',
            ],
            'Pando' => [
                'Abuná',
                'Federico Román',
                'Madre de Dios',
                'Manuripi',
                'Nicolás Suárez',
            ],
            'Potosí' => [
                'Alonzo de Ibáñez',
                'Antonio Quijarro',
                'Bernardino Bilbao',
                'Charcas',
                'Chayanta',
                'Cornelio Saavedra',
                'Daniel Saavedra',
                'Enrique Baldivieso',
                'José María Linares',
                'Modesto Omiste',
                'Nor Chichas',
                'Nor Lípez',
                'Rafael Bustillo',
                'Sud Chichas',
                'Sud Lipez',
                'Tomás Frías',
            ],
            'Santa Cruz' => [
                'Andrés Ibáñez',
                'Ignacio Warnes',
                'José Miguel de Velasco',
                'Ichilo',
                'Chiquitos',
                'Sara',
                'Cordillera',
                'Vallegrande',
                'Florida',
                'Santistevan',
                'Ñuflo de Chávez',
                'Ángel Sandoval',
                'Caballero',
                'Germán Busch',
                'Guarayos',
            ],
            'Tarija' => [
                'Aniceto Arce',
                'Burdet O\'Connor',
                'Cercado',
                'Eustaquio Méndez',
                'Gran Chaco',
                'José María Avilés',
            ],
        ];

        foreach ($listado_divisiones as $k => $v) {
            $p = new App\Provincia;
            $p->provincia = $k;
            $p->id_pais = $pais->id;
            $p->save();

            foreach ($v as $vv) {
                $s = new App\Localidad;
                $s->localidad = $vv;
                $s->id_provincia = $p->id;
                $s->save();
            }
        }

    }
}