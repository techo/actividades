<?php

namespace Tests\Feature;

use App\Actividad;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Reclamo #19: el form de actividad reenvía todo, y una pestaña abierta desde antes
 * pisaba lo que otro había cambiado (el modo pasó "solo" de mixto a automático).
 */
class ActividadGuardadoConcurrenteTest extends TestCase
{
    use RefreshDatabase;

    private function coordinadorCon(Actividad $actividad)
    {
        $this->seed('PermisosSeeder');
        $persona = factory('App\Persona')->create();
        $persona->assignRole('admin');
        $persona->idPaisPermitido = $actividad->idPais;
        $persona->save();

        return $persona;
    }

    /** Payload como el que manda actividad.vue: lo que cargó (+ sus cambios) y el snapshot. */
    private function payload(Actividad $cargada, array $cambios = [])
    {
        $original = $cargada->toArray();

        return array_merge($original, [
            'requiere_ficha_medica' => 0,
            'requiere_estudios' => 0,
            'show_dates' => 1,
            'show_location' => 1,
            'calculaFecha' => 0,
        ], $cambios, ['_original' => $original]);
    }

    /** @test */
    public function una_pestana_vieja_no_pisa_el_modo_que_cambio_otro()
    {
        $actividad = factory('App\Actividad')->create(['confirmacion' => 0, 'pago' => 0]);
        $persona = $this->coordinadorCon($actividad);
        $cargada = Actividad::find($actividad->idActividad);

        // Otro coordinador la pasa a mixto (confirmación + pago) mientras tanto.
        Actividad::where('idActividad', $actividad->idActividad)->update(['confirmacion' => 1, 'pago' => 1, 'montoMin' => 100]);

        // La pestaña vieja guarda otra cosa (la descripción) con el modo viejo 0/0.
        $this->actingAs($persona)
            ->postJson('/admin/ajax/actividades/' . $actividad->idActividad, $this->payload($cargada, ['descripcion' => 'nueva']))
            ->assertStatus(409)
            ->assertJsonFragment(['conflictos' => ['confirmacion', 'pago']]);

        $this->assertDatabaseHas('Actividad', ['idActividad' => $actividad->idActividad, 'confirmacion' => 1, 'pago' => 1]);
    }

    /** @test */
    public function sin_cambios_de_otros_guarda_y_devuelve_el_nuevo_snapshot()
    {
        $actividad = factory('App\Actividad')->create(['confirmacion' => 0, 'pago' => 0]);
        $persona = $this->coordinadorCon($actividad);
        $cargada = Actividad::find($actividad->idActividad);

        $respuesta = $this->actingAs($persona)
            ->postJson('/admin/ajax/actividades/' . $actividad->idActividad, $this->payload($cargada, ['confirmacion' => 1]))
            ->assertStatus(200)
            ->json();

        $this->assertDatabaseHas('Actividad', ['idActividad' => $actividad->idActividad, 'confirmacion' => 1]);

        // Segundo guardado desde la misma pestaña, con la respuesta como snapshot: sin
        // falsos conflictos aunque las fechas vuelvan normalizadas por la DB.
        $segunda = Actividad::find($actividad->idActividad);
        $payload = array_merge($this->payload($segunda, ['descripcion' => 'otra']), ['_original' => $respuesta]);
        $this->actingAs($persona)
            ->postJson('/admin/ajax/actividades/' . $actividad->idActividad, $payload)
            ->assertStatus(200);
    }

    /** @test */
    public function sin_snapshot_se_comporta_como_antes()
    {
        $actividad = factory('App\Actividad')->create(['confirmacion' => 0]);
        $persona = $this->coordinadorCon($actividad);
        $payload = $this->payload(Actividad::find($actividad->idActividad), ['confirmacion' => 1]);
        unset($payload['_original']);

        $this->actingAs($persona)
            ->postJson('/admin/ajax/actividades/' . $actividad->idActividad, $payload)
            ->assertStatus(200);
    }
}
