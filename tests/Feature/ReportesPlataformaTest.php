<?php

namespace Tests\Feature;

use App\IssueReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ReportesPlataformaTest extends TestCase
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

    /** @test */
    public function quien_reporta_puede_declarar_la_plataforma()
    {
        $this->actingAs($this->admin())
            ->postJson('/admin/ajax/reportes', ['type' => 'bug', 'description' => 'Falla en la app', 'platform' => 'app'])
            ->assertStatus(201);

        $this->assertDatabaseHas('issue_reports', ['description' => 'Falla en la app', 'platform' => 'app']);
    }

    /** @test */
    public function la_plataforma_es_opcional_y_se_valida()
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->postJson('/admin/ajax/reportes', ['type' => 'bug', 'description' => 'Sin plataforma'])
            ->assertStatus(201);
        $this->assertDatabaseHas('issue_reports', ['description' => 'Sin plataforma', 'platform' => null]);

        $this->actingAs($admin)
            ->postJson('/admin/ajax/reportes', ['type' => 'bug', 'description' => 'x', 'platform' => 'ios'])
            ->assertStatus(422);
    }

    /** @test */
    public function el_admin_puede_setear_y_filtrar_la_plataforma()
    {
        $admin = $this->admin();
        $report = IssueReport::create(['type' => 'bug', 'status' => 'nuevo', 'description' => 'Link de evaluación roto']);
        IssueReport::create(['type' => 'bug', 'status' => 'nuevo', 'description' => 'Otro', 'platform' => 'web']);

        $this->actingAs($admin)
            ->postJson('/admin/ajax/reportes/' . $report->id, ['platform' => 'ambas'])
            ->assertOk();
        $this->assertEquals('ambas', $report->fresh()->platform);

        $this->actingAs($admin)
            ->getJson('/admin/ajax/reportes?platform=ambas')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $report->id, 'platform' => 'ambas', 'platform_label' => 'Web y App']);

        $this->actingAs($admin)
            ->postJson('/admin/ajax/reportes/' . $report->id, ['platform' => null])
            ->assertOk();
        $this->assertNull($report->fresh()->platform);
    }
}
