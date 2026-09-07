<?php

namespace Tests\Feature\api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * Tarea #45 (upgrade-fase0) — Contrato del formato de fechas en la API mobile.
 *
 * Complementa el test de fechas de ActividadesApiTest: ESE cubre fechas que un
 * Resource formatea explícitamente (->format('d-m-Y')). ESTE cubre el hueco real
 * que la revisión (upgrade-review.md §2.2) marcó: las fechas que se serializan por
 * el mecanismo POR DEFECTO de Eloquent, sin Resource de por medio.
 *
 * api\PersonasController@show devuelve el modelo Persona crudo; sus atributos de
 * $dates (p.ej. primer_acceso_app) se serializan hoy como 'Y-m-d H:i:s'. Laravel 7
 * cambia ese default a ISO-8601 ('2020-05-15T10:30:00.000000Z'), lo que rompería el
 * parseo de la app instalada sin que podamos avisar a los clientes.
 *
 * Este test FIJA el formato legacy como ancla anti-regresión: cuando se llegue a la
 * Fase 2, debe seguir verde gracias al override de serializeDate() legacy. Si se
 * pone rojo, es la señal temprana de que el contrato JSON cambió.
 */
class ContratoFechasApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function el_perfil_serializa_las_fechas_en_formato_legacy_no_iso8601()
    {
        $persona = factory('App\Persona')->create();

        // Fecha conocida en un atributo de $dates del modelo (se serializa por defecto).
        $persona->primer_acceso_app = '2020-05-15 10:30:00';
        $persona->save();

        Passport::actingAs($persona);

        $json = $this->getJson('/api/personas/' . $persona->idPersona)
            ->assertStatus(200)
            ->json();

        // Contrato actual (Laravel 5.7): 'Y-m-d H:i:s'. NO ISO-8601.
        $this->assertRegExp(
            '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
            $json['primer_acceso_app'],
            'La API cambió el formato de fecha por defecto (posible regresión de Laravel 7 → ISO-8601). '
            . 'Ver upgrade-laravel.md §Fase 2: hace falta serializeDate() legacy.'
        );
        // El regex anclado (con espacio, sin 'T' ni microsegundos/Z) ya rechaza
        // el formato ISO-8601 '2020-05-15T10:30:00.000000Z' que introduce Laravel 7.
    }
}
