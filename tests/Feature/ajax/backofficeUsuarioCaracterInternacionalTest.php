<?php

namespace Tests\Feature\ajax;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

class backofficeUsuarioCaracterInternacionalTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
     public function crear_usuario_caracter_internacinoal()
     {
        $this->withoutExceptionHandling();
        
        $actividades = factory('App\Actividad', 4)->create();

        $persona = factory('App\Persona')->create(['nombres' => 'ñombré']);
        $persona->givePermissionTo(Permission::create(['name' => 'ver_backoffice']));

        $this->actingAs($persona)
            ->get('/admin/ajax/search/usuarios?usuario=ñombré')
             ->assertStatus(200)
             ->assertJsonStructure(
                 [
                   '*' => [
                     "idPersona",
                     "dni",
                     "nombre"
                   ]
                 ]
             );
     }
}
