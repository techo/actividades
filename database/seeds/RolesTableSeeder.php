<?php

use Illuminate\Database\Seeder;
use \Spatie\Permission\Models\Role as Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create(['name' => 'usuario_autenticado']);
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'coordinador']);
    }
}
