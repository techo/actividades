<?php

namespace Tests\Feature;

use App\IssueReport;
use App\IssueReportReply;
use App\Mail\MailReporteRespondido;
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

    /** @test */
    public function por_defecto_la_respuesta_se_firma_como_techita_y_guarda_quien_la_escribio()
    {
        Queue::fake();
        $admin = $this->admin();
        $reporte = $this->reporte();

        $this->actingAs($admin)
            ->postJson("/admin/ajax/reportes/{$reporte->id}/responder", ['body' => 'Hola, soy Techita'])
            ->assertStatus(200)
            ->assertJson(['reply' => ['author_name' => 'Techita', 'escrito_por' => $admin->nombreCompleto]]);

        $reply = $reporte->respuestas()->first();
        $this->assertEquals(IssueReportReply::AUTOR_TECHITA, $reply->author_name);
        $this->assertEquals($admin->idPersona, $reply->idPersona);
    }

    /** @test */
    public function se_puede_responder_con_el_nombre_propio()
    {
        Queue::fake();
        $admin = $this->admin();
        $reporte = $this->reporte();

        $this->actingAs($admin)
            ->postJson("/admin/ajax/reportes/{$reporte->id}/responder", ['body' => 'Hola', 'como_techita' => false])
            ->assertStatus(200);

        $this->assertEquals($admin->nombreCompleto, $reporte->respuestas()->first()->author_name);
    }

    /** @test */
    public function el_aviso_de_resuelto_va_firmado_por_techita()
    {
        Queue::fake();
        $reporte = $this->reporte();

        $this->actingAs($this->admin())->postJson("/admin/ajax/reportes/{$reporte->id}", ['status' => 'resuelto']);

        $this->assertEquals(IssueReportReply::AUTOR_TECHITA, $reporte->respuestas()->first()->author_name);
    }

    /** @test */
    public function el_mail_de_techita_sale_con_su_nombre_y_firma()
    {
        $reporte = $this->reporte();
        $reply = IssueReportReply::create([
            'issue_report_id' => $reporte->id, 'author_name' => IssueReportReply::AUTOR_TECHITA,
            'tipo' => IssueReportReply::TIPO_MENSAJE, 'body' => 'Ya está resuelto', 'is_internal' => false,
        ]);

        $mail = new MailReporteRespondido($reporte, $reply);
        $html = $mail->render();
        $mail->build();

        $this->assertEquals(__('email.reporte_remitente_techita'), $mail->from[0]['name']);
        $this->assertContains(__('email.reporte_firma_techita'), $html);
        $this->assertContains(__('email.reporte_respuesta_techita'), $html);
    }
}
