<?php

use Illuminate\Database\Seeder;

/**
 * Países donde opera TECHO, con sus IDs canónicos (los mismos que producción,
 * ej. 13 = Argentina = APP_PAIS_DEFAULT), abreviación y locale.
 *
 * Sin esto el sistema no arranca: el middleware SeleccionarPais necesita que
 * exista el país de APP_PAIS_DEFAULT, y el login usa el país del usuario.
 *
 * Es idempotente (updateOrCreate por id) y funciona tanto sobre una BD con el
 * listado completo de PaisesSeeder como sin él.
 */
class PaisesHabilitadosSeeder extends Seeder
{
    const PAISES = [
        ['id' => 13, 'nombre' => 'Argentina', 'abreviacion' => 'argentina', 'locale' => 'es_AR'],
        ['id' => 29, 'nombre' => 'Bolivia', 'abreviacion' => 'bolivia', 'locale' => 'es_AR'],
        ['id' => 33, 'nombre' => 'Brasil', 'abreviacion' => 'brasil', 'locale' => 'pt'],
        ['id' => 52, 'nombre' => 'Colombia', 'abreviacion' => 'colombia', 'locale' => 'es_AR'],
        ['id' => 60, 'nombre' => 'Costa Rica', 'abreviacion' => 'costarica', 'locale' => 'es_AR'],
        ['id' => 65, 'nombre' => 'República Dominicana', 'abreviacion' => 'republicadominicana', 'locale' => 'es_AR'],
        ['id' => 66, 'nombre' => 'Ecuador', 'abreviacion' => 'ecuador', 'locale' => 'es_AR'],
        ['id' => 68, 'nombre' => 'El Salvador', 'abreviacion' => 'elsalvador', 'locale' => 'es_AR'],
        ['id' => 94, 'nombre' => 'Guatemala', 'abreviacion' => 'guatemala', 'locale' => 'es_AR'],
        ['id' => 102, 'nombre' => 'Honduras', 'abreviacion' => 'honduras', 'locale' => 'es_AR'],
        ['id' => 146, 'nombre' => 'México', 'abreviacion' => 'mexico', 'locale' => 'es_AR'],
        ['id' => 170, 'nombre' => 'Panamá', 'abreviacion' => 'panama', 'locale' => 'es_AR'],
        ['id' => 172, 'nombre' => 'Paraguay', 'abreviacion' => 'paraguay', 'locale' => 'es_AR'],
        ['id' => 173, 'nombre' => 'Perú', 'abreviacion' => 'peru', 'locale' => 'es_AR'],
        ['id' => 229, 'nombre' => 'Uruguay', 'abreviacion' => 'uruguay', 'locale' => 'es_AR'],
        ['id' => 232, 'nombre' => 'Venezuela', 'abreviacion' => 'venezuela', 'locale' => 'es_AR'],
        ['id' => 241, 'nombre' => 'Internacional', 'abreviacion' => 'internacional', 'locale' => 'es_AR'],
        ['id' => 242, 'nombre' => 'Europa', 'abreviacion' => 'europa', 'locale' => 'es_AR'],
    ];

    public function run()
    {
        foreach (static::PAISES as $pais) {
            // updateOrInsert directo: el modelo Pais solo tiene 'nombre' fillable.
            \DB::table('atl_pais')->updateOrInsert(
                ['id' => $pais['id']],
                [
                    'nombre' => $pais['nombre'],
                    'abreviacion' => $pais['abreviacion'],
                    'locale' => $pais['locale'],
                    'habilitado' => 1,
                ]
            );
        }
    }
}
