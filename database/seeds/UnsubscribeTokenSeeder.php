<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnsubscribeTokenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
                DB::statement('UPDATE Persona SET unsubscribe_token = UUID()');
    }
}
