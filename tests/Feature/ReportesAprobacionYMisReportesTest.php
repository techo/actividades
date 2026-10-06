<?php

namespace Tests\Feature;

use App\IssueReport;
use App\IssueReportReply;
use App\Jobs\EnviarMailTransaccionalSes;
use App\Mail\MailReporteRespondido;
use App\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Flujo propuesta → aprobación → respuesta de Techita, y "Mis reportes" (la vista de quien
 * reportó, donde responde porque el mail sale de noreply).
 */
class ReportesAprobacionYMisReportesTest extends TestCase
{
    use RefreshDatabase;

    private function admin()
    {
        $this->seed('PermisosSeeder');
        Permission::findOrCreate('ver_reportes');
        $admin = factory(Persona::class)->create();
        $admin->assignRole('admin');
        $admin->givePermissionTo('ver_reportes');
        return $admin;
    }

    private function coordinador()
    {
        $c = factory(Persona::class)->create();
        $c->assignRole('coordinador');
        return $c;
    }

    private function reporte(Persona $de, array $attrs = [])
    {
        return IssueReport::create(array_merge([
            'type' => 'bug', 'status' => 'nuevo', 'description' => 'No puedo exportar',
            'idPersona' => $de->idPersona, 'reporter_name' => $de->nombreCompleto,
            'reporter_email' => 'reporta@example.org',
        ], $attrs));
    }

    private function propuesta(IssueReport $r, $estado)
    {
        return IssueReportReply::create([
            'issue_report_id' => $r->id, 'author_name' => IssueReportReply::AUTOR_TECHITA,
            'tipo' => IssueReportReply::TIPO_MENSAJE, 'body' => 'Ya está arreglado.',
            'is_internal' => true, 'estado_propuesto' => $estado,
        ]);
    }

    /** @test */
    public function una_nota_interna_guarda_su_estado_propuesto()
    {
        Queue::fake();
        $admin = $this->admin();
        $r = $this->reporte($this->coordinador());

        $this->actingAs($admin)
            ->postJson("/admin/ajax/reportes/{$r->id}/responder", ['body' => 'Propuesta', 'visible' => false, 'estado_propuesto' => 'resuelto'])
            ->assertStatus(200)
            ->assertJson(['reply' => ['is_internal' => true, 'estado_propuesto' => 'resuelto']]);

        Queue::assertNotPushed(EnviarMailTransaccionalSes::class);
        $this->assertEquals('nuevo', $r->fresh()->status);
    }

    /** @test */
    public function aprobar_publica_aplica_el_estado_y_manda_un_solo_mail()
    {
        Queue::fake();
        $admin = $this->admin();
        $r = $this->reporte($this->coordinador());
        $nota = $this->propuesta($r, 'resuelto');

        $this->actingAs($admin)
            ->postJson("/admin/ajax/reportes/{$r->id}/respuestas/{$nota->id}/publicar")
            ->assertStatus(200)
            ->assertJson(['notificado' => true, 'status' => 'resuelto', 'reply' => ['is_internal' => false, 'tipo' => 'resuelto']]);

        Queue::assertPushed(EnviarMailTransaccionalSes::class, 1);
        $this->assertEquals('resuelto', $r->fresh()->status);
        $this->assertNotNull($r->fresh()->resolved_at);
        $this->assertEquals(1, $r->respuestas()->count()); // no se crea un "resuelto" aparte
    }

    /** @test */
    public function aprobar_sin_estado_propuesto_no_cambia_el_estado()
    {
        Queue::fake();
        $admin = $this->admin();
        $r = $this->reporte($this->coordinador(), ['status' => 'triage']);
        $nota = $this->propuesta($r, null);

        $this->actingAs($admin)->postJson("/admin/ajax/reportes/{$r->id}/respuestas/{$nota->id}/publicar")->assertStatus(200);

        $this->assertEquals('triage', $r->fresh()->status);
        Queue::assertPushed(EnviarMailTransaccionalSes::class, 1);
    }

    /** @test */
    public function no_se_puede_aprobar_dos_veces_ni_una_respuesta_de_otro_reporte()
    {
        Queue::fake();
        $admin = $this->admin();
        $c = $this->coordinador();
        $r = $this->reporte($c);
        $otro = $this->reporte($c);
        $nota = $this->propuesta($r, 'resuelto');

        $this->actingAs($admin)->postJson("/admin/ajax/reportes/{$otro->id}/respuestas/{$nota->id}/publicar")->assertStatus(404);
        $this->actingAs($admin)->postJson("/admin/ajax/reportes/{$r->id}/respuestas/{$nota->id}/publicar")->assertStatus(200);
        $this->actingAs($admin)->postJson("/admin/ajax/reportes/{$r->id}/respuestas/{$nota->id}/publicar")->assertStatus(422);

        Queue::assertPushed(EnviarMailTransaccionalSes::class, 1);
    }

    /** @test */
    public function el_mail_lleva_a_mis_reportes_para_responder()
    {
        $this->admin(); // seed de roles
        $r = $this->reporte($this->coordinador());
        $reply = $this->propuesta($r, null);

        $html = (new MailReporteRespondido($r, $reply))->render();

        $this->assertContains(url('/admin/mis-reportes/' . $r->id), $html);
    }

    /** @test */
    public function quien_reporto_ve_su_reporte_y_la_conversacion_pero_no_las_notas_internas()
    {
        $this->admin(); // seed de roles
        $c = $this->coordinador();
        $r = $this->reporte($c);
        IssueReportReply::create(['issue_report_id' => $r->id, 'author_name' => 'Techita', 'tipo' => 'mensaje', 'body' => 'Respuesta visible', 'is_internal' => false]);
        IssueReportReply::create(['issue_report_id' => $r->id, 'author_name' => 'Techita', 'tipo' => 'mensaje', 'body' => 'Nota secreta de triage', 'is_internal' => true]);

        $this->actingAs($c)->get('/admin/mis-reportes')->assertStatus(200)->assertSee('No puedo exportar');
        $this->actingAs($c)->get("/admin/mis-reportes/{$r->id}")
            ->assertStatus(200)
            ->assertSee('Respuesta visible')
            ->assertDontSee('Nota secreta de triage');
    }

    /** @test */
    public function nadie_ve_reportes_ajenos()
    {
        $this->admin();
        $r = $this->reporte($this->coordinador());
        $otro = $this->coordinador();

        $this->actingAs($otro)->get("/admin/mis-reportes/{$r->id}")->assertStatus(404);
        $this->actingAs($otro)->post("/admin/mis-reportes/{$r->id}/responder", ['body' => 'hola'])->assertStatus(404);
    }

    /** @test */
    public function quien_reporto_responde_reabre_si_estaba_cerrado_y_la_bandeja_lo_marca()
    {
        $admin = $this->admin();
        $c = $this->coordinador();
        $r = $this->reporte($c, ['status' => 'resuelto', 'resolved_at' => now()]);

        $this->actingAs($c)
            ->post("/admin/mis-reportes/{$r->id}/responder", ['body' => 'Me sigue pasando'])
            ->assertRedirect("/admin/mis-reportes/{$r->id}");

        $this->assertDatabaseHas('issue_report_replies', [
            'issue_report_id' => $r->id, 'tipo' => IssueReportReply::TIPO_REPORTANTE,
            'body' => 'Me sigue pasando', 'is_internal' => 0, 'idPersona' => $c->idPersona,
        ]);
        $this->assertEquals('triage', $r->fresh()->status);
        $this->assertNull($r->fresh()->resolved_at);

        $fila = collect($this->actingAs($admin)->getJson('/admin/ajax/reportes')->json('data'))->firstWhere('id', $r->id);
        $this->assertTrue($fila['respuesta_pendiente']);
    }
}
