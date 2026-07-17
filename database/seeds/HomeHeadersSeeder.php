<?php

use App\HomeHeader;
use Illuminate\Database\Seeder;

/**
 * Header de la home pública para cada país habilitado. Sin una fila en
 * home_headers para el país activo, toda vista pública (incluido /login)
 * tira 500 ("Trying to get property 'imagen' of non-object").
 *
 * Textos tomados de producción. Idempotente (updateOrCreate por idPais).
 */
class HomeHeadersSeeder extends Seeder
{
    public function run()
    {
        $porLocale = [
            'es_AR' => [
                'header' => 'SI TE DA LO MISMO, ESTÁS HACIENDO MAL LAS CUENTAS',
                'subHeader' => 'ANOTATE Y PARTICIPÁ',
            ],
            'pt' => [
                'header' => 'POR UMA SOCIEDADE JUSTA E SEM POBREZA!',
                'subHeader' => 'INSCREVA-SE E PARTICIPE',
            ],
        ];

        foreach (PaisesHabilitadosSeeder::PAISES as $pais) {
            $textos = $porLocale[$pais['locale']] ?? $porLocale['es_AR'];

            HomeHeader::updateOrCreate(
                ['idPais' => $pais['id']],
                [
                    'header' => $textos['header'],
                    'subHeader' => $textos['subHeader'],
                    'imagen' => '/img/hero-slim.jpg',
                ]
            );
        }
    }
}
