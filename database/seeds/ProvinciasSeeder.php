<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinciasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ProvinciasArgentinaSeeder::class);
        $this->call(ProvinciasBoliviaSeeder::class);
        $this->call(ProvinciasBrasilSeeder::class);
        $this->call(ProvinciasChileSeeder::class);
        $this->call(ProvinciasColombiaSeeder::class);
        $this->call(ProvinciasCostaRicaSeeder::class);
        $this->call(ProvinciasEcuadorSeeder::class);
        $this->call(ProvinciasElSalvadorSeeder::class);
        $this->call(ProvinciasGuatemalaSeeder::class);
        $this->call(ProvinciasHondurasSeeder::class);
        $this->call(ProvinciasMexicoSeeder::class);
        $this->call(ProvinciasNicaraguaSeeder::class);
        $this->call(ProvinciasPanamaSeeder::class);
        $this->call(ProvinciasParaguaySeeder::class);
        $this->call(ProvinciasPuertoRicoSeeder::class);
        $this->call(ProvinciasPeruSeeder::class);
        $this->call(ProvinciasRepublicaDominicanaSeeder::class);
        $this->call(ProvinciasUruguaySeeder::class);
        $this->call(ProvinciasVenezuelaSeeder::class);
    }
}
