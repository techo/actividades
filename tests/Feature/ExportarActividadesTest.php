<?php

namespace Tests\Feature;

use App\ActividadFactory;
use App\Exports\ActividadesExport;
use App\Exports\MisActividadesExport;
use App\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/**
 * Task 48 — export de actividades desde la vista por oficina (reclamo #3 de producción).
 */
class ExportarActividadesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function exportar_desde_la_vista_por_oficina_trae_solo_esa_oficina()
    {
        $this->seed('PermisosSeeder');
        $admin = factory(Persona::class)->create();
        $admin->assignRole('admin');
        $pais = factory('App\Pais')->create();
        $salta = factory('App\Oficina')->create();
        $otra = factory('App\Oficina')->create();

        $deSalta = app(ActividadFactory::class)->creadaPor($admin)->conPais($pais)->create(['idOficina' => $salta->id]);
        app(ActividadFactory::class)->creadaPor($admin)->conPais($pais)->create(['idOficina' => $otra->id]);

        Excel::fake();

        $this->actingAs($admin)
            ->get("/admin/actividades/oficina/{$salta->id}/exportar?filter=")
            ->assertStatus(200);

        Excel::assertDownloaded('actividades.xlsx', function (ActividadesExport $export) use ($deSalta) {
            $ids = $export->collection()->pluck('id')->all();
            return $ids === [$deSalta->idActividad];
        });
    }

    /** @test */
    public function los_encabezados_coinciden_con_las_columnas()
    {
        foreach ([new ActividadesExport(), new MisActividadesExport()] as $export) {
            $actividad = new \App\Actividad();
            $this->assertCount(count($export->headings()), $export->map($actividad), get_class($export));
        }
    }
}
