<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocalidadGenericaSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $provincias = DB::table('atl_provincias')->get();
        foreach ($provincias as $provincia) {
            $generica = DB::table('atl_localidades')
                ->where('localidad', '=', 'Sin especificar')
                ->where('id_provincia', $provincia->id)
                ->get();
            if ($generica->count() == 0) {
                DB::table('atl_localidades')
                    ->insert(['id_provincia' => $provincia->id, 'localidad' => 'Sin especificar']);
            }
        }
    }
}
