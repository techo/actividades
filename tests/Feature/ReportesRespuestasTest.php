<?php

namespace Tests\Feature;

use App\IssueReport;
use App\Jobs\EnviarMailTransaccionalSes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Respuestas de la bandeja de reportes y aviso por mail (SES) a quien reportó.
 * Regresión: EnviarMailTransaccionalSes::dispatch() no existía (el job no usa
 * Dispatchable) → 500, el mail no salía y la respuesta quedaba marcada como notificada.
 */
class ReportesRespuestasTest extends TestCase
{
    use RefreshDatabase;

    private function admin()
    {
        $this->seed('PermisosSeeder');
        Permission::findOrCreate('ver_reportes');
        $admin = factory('App\Persona')->create();
        $admin->assignRole('admin');
        $admin->givePermissionTo('ver_reportes');
        return $admin;
    }

    private function reporte(array $attrs = [])
    {
        return IssueReport::create(array_merge([
            'type' => 'bug', 'status' => 'nuevo', 'description' => 'No anda',
            'reporter_email' => 'quien.reporta@example.org', 'reporter_name' => 'Quien Reporta',
        ], $attrs));
    }

    /** @test */
    public function una_respuesta_visible_encola_el_mail_y_queda_notificada()
    {
        Queue::fake();
        $reporte = $this->reporte();

        $this->actingAs($this->admin())
            ->postJson("/admin/ajax/reportes/{$reporte->id}/responder", ['body' => 'Ya lo estamos viendo', 'visible' => true])
            ->assertStatus(200)
            ->assertJson(['notificado' => true]);

        Queue::assertPushed(EnviarMailTransaccionalSes::class, function ($job) {
            return $job->to === 'quien.reporta@example.org';
        });
        $this->assertNotNull($reporte->respuestas()->first()->notified_at);
    }

    /** @test */
    public function una_nota_interna_no_avisa()
    {
        Queue::fake();
        $reporte = $this->reporte();

        $this->actingAs($this->admin())
            ->postJson("/admin/ajax/reportes/{$reporte->id}/responder", ['body' => 'Borrador de Techita', 'visible' => false])
            ->assertStatus(200)
            ->assertJson(['notificado' => false]);

        Queue::assertNotPushed(EnviarMailTransaccionalSes::class);
        $this->assertNull($reporte->respuestas()->first()->notified_at);
        $this->assertTrue((bool) $reporte->respuestas()->first()->is_internal);
    }

    /** @test */
    public function marcar_resuelto_avisa_a_quien_reporto()
    {
        Queue::fake();
        $reporte = $this->reporte();

        $this->actingAs($this->admin())
            ->postJson("/admin/ajax/reportes/{$reporte->id}", ['status' => 'resuelto'])
            ->assertStatus(200)
            ->assertJson(['notificado' => true]);

        Queue::assertPushed(EnviarMailTransaccionalSes::class, 1);
    }

    /** @test */
    public function sin_email_de_contacto_no_avisa()
    {
        Queue::fake();
        $reporte = $this->reporte(['reporter_email' => null]);

        $this->actingAs($this->admin())
            ->postJson("/admin/ajax/reportes/{$reporte->id}/responder", ['body' => 'Hola'])
            ->assertStatus(200)
            ->assertJson(['notificado' => false]);

        Queue::assertNotPushed(EnviarMailTransaccionalSes::class);
    }
}
