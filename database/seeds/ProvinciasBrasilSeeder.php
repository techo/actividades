<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinciasBrasilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pais = App\Pais::where('nombre', 'Brasil')->first();

        $listado_provincias = [
            'Acre',
            'Alagoas',
            'Amapá',
            'Amazonas',
            'Bahía',
            'Ceará',
            'Espírito Santo',
            'Goiás',
            'Maranhão',
            'Mato Grosso',
            'Mato Grosso del Sur',
            'Minas Gerais',
            'Estado de Pará',
            'Paraíba',
            'Paraná',
            'Estado de Pernambuco',
            'Piauí',
            'Río de Janeiro',
            'Río Grande del Norte',
            'Río Grande del Sur',
            'Rondônia',
            'Roraima',
            'Santa Catarina',
            'São Paulo',
            'Sergipe',
            'Tocantins',
            'Distrito Federal'
        ];

        foreach ($listado_provincias as $v) {
            $p = new App\Provincia;
            $p->provincia = $v;
            $p->id_pais = $pais->id;
            $p->save();
        }

    }
}