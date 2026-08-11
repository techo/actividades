<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Task 35 — Segurizar GET /admin/logs/{proceso}.
 *
 * La ruta vivía dentro del grupo /admin (verified + auth + can:accesoBackoffice)
 * pero sin gate de rol: cualquier usuario de backoffice (p.ej. coordinador, que
 * tiene ver_backoffice) podía descargar los logs. El audit B-1 pide role:admin.
 *
 * `coordinador` tiene ver_backoffice (pasa el gate del grupo) pero no es admin:
 * por eso su 403 prueba específicamente el nuevo middleware role:admin.
 */
class LogsSegurizadosTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function coordinador_no_admin_no_puede_descargar_logs()
    {
        $this->seed('PermisosSeeder');

        $coordi = factory('App\Persona')->create();
        $coordi->assignRole('coordinador');

        $this->actingAs($coordi)
            ->get('/admin/logs/importar')
            ->assertForbidden(); // 403 por role:admin (ya pasó accesoBackoffice)
    }

    /** @test */
    public function admin_puede_descargar_logs()
    {
        $this->seed('PermisosSeeder');

        $admin = factory('App\Persona')->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get('/admin/logs/importar')
            ->assertStatus(200)
            ->assertHeader('Content-Disposition', 'attachment; filename="errores.txt"');
    }

    /** @test */
    public function usuario_no_autenticado_no_accede_a_logs()
    {
        // El grupo /admin exige auth: un invitado no llega a la descarga (redirect a login).
        $this->get('/admin/logs/importar')->assertStatus(302);
    }
}
