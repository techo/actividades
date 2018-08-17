<?php

use Illuminate\Database\Seeder;

class UnsubscribeTokenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Persona::chunk(1000, function ($personas) {
            foreach ($personas as $persona) {
                $persona->update(['unsubscribe_token' => Webpatser\Uuid\Uuid::generate()->string]);
            }
        });
    }
}
